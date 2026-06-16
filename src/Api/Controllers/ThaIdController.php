<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Core\Auth;
use App\Core\AuthCookie;
use App\Core\Jwt;
use App\Core\Router;
use App\Models\User;
use App\Services\ThaIdAuthService;
use App\Services\ThaIdConfig;

/**
 * ThaID (DOPA) OAuth2 login endpoints.
 *
 *   GET /api/v1/auth/thaid/status   → JSON { enabled, mock }     (XHR; SPA gate)
 *   GET /api/v1/auth/thaid/login    → 302 to DOPA (or mock login) (top-level nav)
 *   GET /api/v1/auth/thaid/callback → 302 to SPA '/'              (top-level nav)
 *
 * login/callback are browser navigations (not XHR) so they emit redirects, not
 * JSON, and do NOT run CORS. On success they mint BOTH the SPA JWT cookie and a
 * PHP session — a strict superset of the legacy mock, so ThaID users keep access
 * to the remaining session-based legacy pages (/budgets, /files).
 *
 * OAuth state/PKCE-verifier live in $_SESSION between login() and callback().
 * This survives the cross-site DOPA→callback top-level GET ONLY because the
 * session cookie is SameSite=Lax (config/app.php). Do NOT switch the session
 * cookie to Strict without moving this state into a dedicated Lax cookie.
 */
final class ThaIdController
{
    private const STATE_TTL_SECONDS = 600;

    public function __construct(
        private readonly ThaIdConfig $cfg = new ThaIdConfig(),
        private readonly ThaIdAuthService $service = new ThaIdAuthService(),
    ) {}

    /** GET /api/v1/auth/thaid/status — tells the SPA whether to show the button. */
    public function status(): void
    {
        CorsMiddleware::apply();
        // exit=false: nothing runs after this in the request, and it keeps the
        // method unit-testable (assert via ApiResponse::$lastBody).
        ApiResponse::ok([
            'enabled' => $this->cfg->isEnabled(),
            'mock'    => $this->cfg->isMock() && !$this->cfg->isProd(),
        ], [], false);
    }

    /** GET /api/v1/auth/thaid/login — start the flow (or run the dev mock). */
    public function login(): void
    {
        if (!$this->cfg->isEnabled()) {
            // Dormant: a top-level navigation gets a redirect, not a 403 body.
            Router::redirect('/');
            return;
        }

        // Dev mock path (never reachable in production — isEnabled() guards it).
        if ($this->cfg->isMock() && !$this->cfg->isProd()) {
            try {
                // mockThaIDLogin() ALREADY establishes the PHP session (calls
                // Auth::login internally) — so here we add only the JWT cookie,
                // not a second Auth::login (which would regenerate twice).
                $user = Auth::mockThaIDLogin();
                $this->mintJwtCookie($user);
                error_log("[thaid] mock_login_success user_id={$user['id']}");
            } catch (\Throwable $e) {
                error_log('[thaid] mock_login_failed: ' . $e->getMessage());
                $_SESSION['flash_error'] = 'เข้าสู่ระบบด้วย ThaID (Mock) ไม่สำเร็จ';
            }
            Router::redirect('/');
            return;
        }

        // Real flow. Rotate the session id BEFORE writing the OAuth state so a
        // pre-set (fixated) session id can't carry the state through the
        // DOPA→callback round trip — closes the session-fixation window.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $begin = $this->service->beginLogin();
        $_SESSION['thaid_oauth'] = [
            'state'         => $begin['state'],
            'code_verifier' => $begin['code_verifier'],
            'ts'            => time(),
        ];
        header('Location: ' . $begin['url'], true, 302);
        exit;
    }

    /** GET /api/v1/auth/thaid/callback — exchange code, mint session, land on SPA. */
    public function callback(): void
    {
        // 1) Provider-side errors arrive as ?error=... — handle first. Log the
        //    sanitized error CODE only (never error_description: possible PII).
        if (!empty($_GET['error'])) {
            // Cap before sanitizing so a long crafted value can't flood the log.
            $errCode = preg_replace('/[^a-z_]/', '', substr((string) $_GET['error'], 0, 64));
            error_log("[thaid] auth_error code={$errCode}");
            $_SESSION['flash_error'] = 'เข้าสู่ระบบด้วย ThaID ไม่สำเร็จ';
            Router::redirect('/');
            return;
        }

        // 2) Read + immediately consume the one-time OAuth state (atomic with
        //    the read; survives even if a later step throws).
        $sess = $_SESSION['thaid_oauth'] ?? null;
        unset($_SESSION['thaid_oauth']);

        if (!is_array($sess) || (time() - (int) ($sess['ts'] ?? 0)) > self::STATE_TTL_SECONDS) {
            error_log('[thaid] login_failed: missing_or_expired_state');
            $_SESSION['flash_error'] = 'เซสชันการเข้าสู่ระบบ ThaID หมดอายุ กรุณาลองใหม่';
            Router::redirect('/');
            return;
        }

        try {
            $user = $this->service->completeLogin(
                (string) ($_GET['code'] ?? ''),
                (string) ($_GET['state'] ?? ''),
                (string) ($sess['state'] ?? ''),
                $sess['code_verifier'] ?? null,
            );

            $this->mintSession($user);
            User::updateLastLogin((int) $user['id']);
            error_log("[thaid] login_success user_id={$user['id']}");
            Router::redirect('/');
        } catch (\Throwable $e) {
            error_log('[thaid] login_failed: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'เข้าสู่ระบบด้วย ThaID ไม่สำเร็จ';
            Router::redirect('/');
        }
    }

    /**
     * Mint the SPA JWT cookie AND the PHP session for a resolved user (real
     * flow). Auth::login regenerates the session id — runs only after the
     * one-time OAuth state has already been consumed above.
     *
     * @param array<string,mixed> $user
     */
    private function mintSession(array $user): void
    {
        $this->mintJwtCookie($user);
        Auth::login($user);
    }

    /**
     * Mint only the SPA JWT cookie (the mock path's session is already
     * established by Auth::mockThaIDLogin, so it must not Auth::login twice).
     *
     * @param array<string,mixed> $user
     */
    private function mintJwtCookie(array $user): void
    {
        $api = require __DIR__ . '/../../../config/api.php';
        $ttl = (int) ($api['jwt_ttl'] ?? 3600);

        $token = Jwt::issue((int) $user['id'], [
            'email' => (string) ($user['email'] ?? ''),
            'role'  => (string) ($user['role'] ?? 'viewer'),
        ]);
        AuthCookie::set($token, time() + $ttl);
    }
}
