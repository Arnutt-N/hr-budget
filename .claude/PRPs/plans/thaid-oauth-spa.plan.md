# Plan: ThaID OAuth2 Login (config-gated) with JWT/SPA Handoff

## Summary
Build the real **Thai Digital ID (ThaID / DOPA `imauth.bora.dopa.go.th`) OAuth2 authorization-code login** as a feature that is **dormant by default** and activated when a superadmin/admin supplies credentials via env (`THAID_CLIENT_ID` / `THAID_CLIENT_SECRET` / `THAID_REDIRECT_URI`). On success the flow resolves a local user (find-or-create), mints the existing JWT httpOnly cookie (`hr_budget_token`) **and** a PHP session, then redirects into the Vue SPA shell at `/`. When unconfigured the SPA hides the ThaID button (via a status endpoint) and the endpoints refuse to start the real flow; the existing `APP_ENV`-gated mock remains for dev.

## User Story
As a **government employee with a ThaID account**, I want to **sign in to the HR-budget SPA with ThaID** instead of an email/password, so that **I use my verified national identity and avoid a separate password** — and as a **system admin**, I want it **off until I provide official DOPA credentials**, so that **the integration never half-works in production**.

## Problem → Solution
**Current:** `/thaid/login` (legacy web route) is an `APP_ENV`-gated **mock** that logs into the PHP session only and has no SPA presence — a documented parity gap. The SPA login page is email/password only. → **Desired:** A real, standards-based ThaID OAuth2 flow exposed to the SPA, gated behind admin-supplied env credentials, that mints the SPA's JWT cookie (+ session for legacy pages) and is fully unit-tested with a mock HTTP client (no live network in CI).

## Metadata
- **Complexity**: Large (security-sensitive; ~18 files)
- **Source PRD**: N/A — net-new post-cutover security feature (PRD `vue-spa-refactor` is fully complete)
- **PRD Phase**: N/A (standalone follow-up #3 of 3 after the Phase 6 cutover)
- **Estimated Files**: ~18 (12 backend, 4 frontend, 1 migration, 1 seed edit) + 4 test files

---

## UX Design

### Before
```
┌──────────────────────────────┐
│  ระบบบริหารงบประมาณบุคลากร     │
│  [ อีเมล ............... ]     │
│  [ รหัสผ่าน ............ ]     │
│  [     เข้าสู่ระบบ        ]     │   ← email/password only
└──────────────────────────────┘
ThaID = /thaid/login (mock, session-only, no SPA button)
```

### After (when THAID configured)
```
┌──────────────────────────────┐
│  ระบบบริหารงบประมาณบุคลากร     │
│  [ อีเมล ............... ]     │
│  [ รหัสผ่าน ............ ]     │
│  [     เข้าสู่ระบบ        ]     │
│  ───────── หรือ ─────────      │
│  [ 🪪  เข้าสู่ระบบด้วย ThaID ]  │   ← shown only when status.enabled
└──────────────────────────────┘
   │ click → window.location = /api/v1/auth/thaid/login
   ▼
 302 → DOPA authorize  → user approves → 302 back to
 /api/v1/auth/thaid/callback?code&state
   → exchange code → userinfo → find-or-create user
   → set hr_budget_token cookie + PHP session → 302 "/"
   → SPA boots, bootstrap() hits /auth/me → lands on /dashboard
```

### After (when NOT configured)
```
Identical to "Before" — status.enabled=false → no ThaID button.
/api/v1/auth/thaid/login returns 403 (real) and the mock only runs when APP_ENV≠production.
```

### Interaction Changes
| Touchpoint | Before | After | Notes |
|---|---|---|---|
| SPA login page | email/password only | + conditional ThaID button | gated by `GET /api/v1/auth/thaid/status` |
| ThaID entry | `/thaid/login` (mock, session) | `/api/v1/auth/thaid/login` (real or mock) | legacy route 302-redirects to the new one |
| Post-ThaID landing | SPA shell `/` (session only) | SPA shell `/` (JWT cookie **+** session) | JWT lets the SPA API work; session keeps legacy `/budgets`,`/files` working |

---

## Mandatory Reading

| Priority | File | Lines | Why |
|---|---|---|---|
| P0 | `src/Api/Controllers/AuthController.php` | 32-101 | JWT-cookie mint pattern + `setTokenCookie()` to extract/share |
| P0 | `src/Services/AuthService.php` | 25-67 | Service shape: returns DTO, `Jwt::issue(id,{email,role})`, audit-log-on-failure |
| P0 | `src/Core/Jwt.php` | 25-37 | `Jwt::issue(int $userId, array $claims)` contract |
| P0 | `src/Api/Middleware/AuthMiddleware.php` | 26-100 | `COOKIE_NAME='hr_budget_token'`, token resolution, CSRF header rule |
| P0 | `config/auth.php` | 30-39 | Existing `thaid` config scaffold (real DOPA URLs already present) |
| P0 | `src/Core/Auth.php` | 99-111, 248-279 | `Auth::login($user)` (session) + existing `mockThaIDLogin()` to preserve |
| P1 | `src/Api/Responses/ApiResponse.php` | 23-93 | Response envelope + `$exit=false` test seam |
| P1 | `src/Dtos/LoginRequestDto.php` | all | DTO style: `final`, `readonly`, `fromRequest()`/`validate()` |
| P1 | `src/Models/User.php` | 19-89 | `find`, `findByEmail`, `create` (hashes password) |
| P1 | `tests/Unit/Services/BudgetExecutionServiceTest.php` | all | SQLite-in-memory test harness pattern (setInstance/resetInstance) |
| P1 | `routes/web.php` | 42-44, 173-184 | API auth route block + legacy `/thaid/login` + `/logout` |
| P1 | `frontend/src/stores/auth.ts` | all | `bootstrap()`/`login()` cookie flow the ThaID redirect plugs into |
| P1 | `frontend/src/pages/LoginPage.vue` | all | Where the ThaID button + status fetch are added |
| P2 | `src/Core/SecurityHeaders.php` | all | Header helper conventions (pure-method + apply pattern) |
| P2 | `database/hr_budget_only.sql` | 1698-1713 | `users` CREATE to extend with `thaid_sub` (CI seed) |

## External Documentation

| Topic | Source | Key Takeaway |
|---|---|---|
| OAuth2 Authorization Code + PKCE | RFC 6749 §4.1, RFC 7636 | `state` (CSRF) is mandatory; PKCE `S256` is defense-in-depth for code interception |
| ThaID / DOPA OIDC | `imauth.bora.dopa.go.th/api/v2/oauth2/*` (in `config/auth.php`) | authorize + token endpoints known; **userinfo URL, scope, and userinfo field names need confirmation from DOPA onboarding docs** — isolate in the adapter |
| Token-endpoint client auth | RFC 6749 §2.3.1 | DOPA likely uses `client_secret_basic` (HTTP Basic) — make the auth style configurable, default Basic |

> RESEARCH note: exact DOPA response field names (sub/pid, name, email) are **not verifiable from here** (no live creds/network). The `ThaIdProvider` adapter centralizes every DOPA-specific detail behind configurable field names so onboarding can adjust without touching service/controller logic. This is the single deliberate unknown; everything else uses established internal patterns.

---

## Patterns to Mirror

### SERVICE_PATTERN (returns DTO, issues JWT, audit-logs)
```php
// SOURCE: src/Services/AuthService.php:25-56
final class AuthService {
    public function authenticate(string $email, string $password): ?AuthResponseDto {
        $user = User::findByEmail($email);
        if ($user === null) { self::logFailure($email, 'user_not_found'); return null; }
        // ...
        $token = Jwt::issue((int) $user['id'], ['email' => ..., 'role' => ...]);
        return new AuthResponseDto(token: $token, expiresIn: (int)$cfg['jwt_ttl'], user: $user);
    }
}
```

### COOKIE_MINT (to extract into a shared helper)
```php
// SOURCE: src/Api/Controllers/AuthController.php:79-101
$secure = isset($_ENV['COOKIE_SECURE']) ? $_ENV['COOKIE_SECURE']==='true'
        : ($_ENV['APP_ENV'] ?? '') === 'production';
setcookie(AuthMiddleware::COOKIE_NAME, $value, [
    'expires'=>$expires,'path'=>'/','httponly'=>true,'samesite'=>'Strict','secure'=>$secure,
]);
```

### CONTROLLER_PATTERN (CORS → parse → ApiResponse, try/catch + error_log)
```php
// SOURCE: src/Api/Controllers/BudgetExecutionController.php (this branch)
public function report(): void {
    CorsMiddleware::apply(); AuthMiddleware::require();
    try { /* ... */ ApiResponse::ok($data); }
    catch (\Throwable $e) { error_log("[X::report] {$e->getMessage()}"); ApiResponse::error('...',500); }
}
```

### DTO_PATTERN
```php
// SOURCE: src/Dtos/LoginRequestDto.php:12-39
final class LoginRequestDto {
    public function __construct(public readonly string $email, public readonly string $password) {}
    public function validate(): array { /* field=>thai msg */ }
    public static function fromRequest(): self { /* json_decode php://input */ }
}
```

### LOGGING_PATTERN (audit to error_log; never expose; strip CR/LF)
```php
// SOURCE: src/Api/Middleware/AuthMiddleware.php:106-113
$path = preg_replace('/[\r\n]/', '', $_SERVER['REQUEST_URI'] ?? '?');
error_log("[auth] api_denied reason={$reason} path={$path} ip={$ip}{$extra}");
```

### TEST_STRUCTURE (SQLite in-memory; setInstance/resetInstance)
```php
// SOURCE: tests/Unit/Services/BudgetExecutionServiceTest.php
protected function setUp(): void {
    $pdo = new \PDO('sqlite::memory:');
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    // CREATE TABLE ...; seed();
    Database::setInstance($pdo);
}
protected function tearDown(): void { Database::resetInstance(); }
```

### CONFIG_PATTERN (env with safe default, derived flags)
```php
// SOURCE: config/api.php:11-21  &  config/auth.php:30-39
'jwt_secret' => $_ENV['JWT_SECRET'] ?? '',
'thaid' => ['client_id' => $_ENV['THAID_CLIENT_ID'] ?? '', ...],
```

---

## Files to Change

| File | Action | Justification |
|---|---|---|
| `config/auth.php` | UPDATE | Extend `thaid` block: env-driven `enabled` (derived from creds), `mock`, `userinfo_url`, `scope`, `redirect_uri`, `pkce`, `client_auth` |
| `src/Core/Http/HttpClientInterface.php` | CREATE | Tiny HTTP contract so DOPA calls are mockable in unit tests |
| `src/Core/Http/CurlHttpClient.php` | CREATE | Real impl (curl ext; `SSL_VERIFYPEER=true`); no new composer dep |
| `src/Core/Http/HttpResponse.php` | CREATE | `readonly {int status, string body}` value object |
| `src/Core/AuthCookie.php` | CREATE | DRY the JWT-cookie set/clear (shared by AuthController + ThaIdController) |
| `src/Dtos/ThaIdIdentityDto.php` | CREATE | Normalized identity `{sub, nameTh, email}` from DOPA userinfo |
| `src/Services/ThaIdConfig.php` | CREATE | Reads `config/auth.php` → `isEnabled()`, `isMock()`, getters; the feature gate |
| `src/Services/ThaIdProvider.php` | CREATE | OAuth2 protocol adapter: `authorizeUrl()`, `exchangeCode()`, `fetchUserInfo()` (all DOPA specifics here) |
| `src/Services/ThaIdAuthService.php` | CREATE | Orchestration: state/PKCE begin, callback → exchange → userinfo → find-or-create user |
| `src/Api/Controllers/ThaIdController.php` | CREATE | `status()` JSON, `login()` 302, `callback()` 302 |
| `src/Models/User.php` | UPDATE | Add `findByThaidSub()` + allow `thaid_sub` in create (already passthrough) |
| `routes/web.php` | UPDATE | Add 3 `/api/v1/auth/thaid/*` routes; legacy `/thaid/login` 302 → new login |
| `src/Api/Controllers/AuthController.php` | UPDATE | Use `AuthCookie` helper (remove private `setTokenCookie`) |
| `src/Controllers/AuthController.php` | **DELETE** | Legacy web ThaID wrapper — orphaned once `/thaid/login` becomes a 302 stub (review H2-arch); recoverable via `pre-spa-cutover` tag |
| `database/migrations/065_add_thaid_sub_to_users.sql` | CREATE | `ALTER TABLE users ADD thaid_sub VARCHAR(64) NULL UNIQUE` |
| `database/hr_budget_only.sql` | UPDATE | Add `thaid_sub` to `users` CREATE (CI seed parity) |
| `frontend/src/types/api.ts` | UPDATE | Add `ThaidStatus { enabled, mock }` |
| `frontend/src/api/auth.ts` | CREATE | `fetchThaidStatus()` + `thaidLoginUrl()` helper |
| `frontend/src/pages/LoginPage.vue` | UPDATE | Conditional ThaID button + divider + onMounted status fetch |
| `public/app/**` | REBUILD | Deploy build artifact (`VITE_BASE=/hr_budget/public/app/ npm run build`) |
| `tests/Unit/Services/ThaIdProviderTest.php` | CREATE | authorizeUrl/exchange/userinfo with FakeHttpClient |
| `tests/Unit/Services/ThaIdAuthServiceTest.php` | CREATE | SQLite find-or-create + state validation |
| `tests/Unit/Services/ThaIdConfigTest.php` | CREATE | enabled/disabled/mock derivation |
| `tests/Unit/Api/ThaIdControllerStatusTest.php` | CREATE | **(review M3-arch)** config-injected `status()` asserts JSON shape via `ApiResponse::$lastBody` (`$exit=false`); lands in `tests/Unit/Api` which CI runs — cheap controller coverage without fighting redirects/session |
| `tests/e2e/api/thaid-status.spec.ts` | CREATE | Deterministic: `APP_ENV=testing` + no `THAID_*` → `{enabled:false}` (proves dormant default) |

## NOT Building
- **No real DOPA round-trip in CI** — unit tests use a FakeHttpClient; live verification is a manual post-deploy step by the admin who holds the creds.
- **No rate limiting** — the app has none anywhere; note as deferred (same posture as `/api/v1/auth/login`).
- **No ThaID account linking UI** — a logged-in user cannot attach ThaID to an existing email account from settings; mapping is automatic by `thaid_sub`/email at login time only.
- **No id_token JWT signature verification** — identity is taken from the userinfo endpoint over TLS (access-token-authenticated), not by locally validating a DOPA-signed id_token (would require DOPA JWKS; deferred, noted as a hardening follow-up).
- **No logout-from-DOPA (RP-initiated logout)** — local logout clears our cookie+session only.
- **No change to password login behavior** beyond the cookie-helper refactor.
- **No removal of legacy `/budgets`/`/files`** — unrelated; tracked separately.

---

## Step-by-Step Tasks

### Task 1: HTTP client seam
- **ACTION**: Create `src/Core/Http/{HttpResponse,HttpClientInterface,CurlHttpClient}.php`.
- **IMPLEMENT**: `HttpResponse` = `final class` with `public function __construct(public readonly int $status, public readonly string $body){}`. `HttpClientInterface::request(string $method, string $url, array $opts): HttpResponse` where `$opts` may hold `headers` (list of `Name: value`), `form` (array → urlencoded body), `basic_auth` ([user,pass]). `CurlHttpClient` = **`final class`** implementing with curl, **`CURLOPT_SSL_VERIFYPEER=true`, `CURLOPT_SSL_VERIFYHOST=2`, `CURLOPT_TIMEOUT=15`, `CURLOPT_RETURNTRANSFER=true`**, no `CURLOPT_FOLLOWLOCATION`. **(review L2)** Assert the curl extension is present in the constructor: `if (!\function_exists('curl_init')) throw new \RuntimeException('ext-curl required for ThaID');` (CI has curl; some prod hosts may not).
- **MIRROR**: namespace + `final class` style from `src/Core/SecurityHeaders.php`.
- **GOTCHA**: never log `$opts['form']` or `basic_auth` (carries client_secret). On curl error return `new HttpResponse(0, '')` and let the caller treat status 0 / non-2xx as failure.
- **VALIDATE**: `php -l` each file; `CurlHttpClient` not unit-tested (it's the I/O boundary) — it's exercised only behind the interface.

### Task 2: AuthCookie helper (DRY refactor)
- **ACTION**: Create `src/Core/AuthCookie.php`; refactor `AuthController` to use it.
- **IMPLEMENT**: `AuthCookie::set(string $token, int $expires): void` and `AuthCookie::clear(): void` containing exactly the logic from `AuthController::setTokenCookie` (lines 79-101), including the `headers_sent()` guard + `error_log` + `APP_ENV==='testing'` skip and the `COOKIE_SECURE` derivation. Then in `AuthController` replace `self::setTokenCookie(...)` calls (login/logout) with `AuthCookie::set/clear` and delete the private method.
- **MIRROR**: `src/Api/Controllers/AuthController.php:79-101` verbatim.
- **GOTCHA**: keep `AuthMiddleware::COOKIE_NAME` as the single source of the cookie name. Do NOT change SameSite/secure semantics — password login tests depend on them.
- **VALIDATE**: `vendor/bin/phpunit --testsuite Unit` (existing auth tests still green).

### Task 3: ThaID config gate
- **ACTION**: **REPLACE** the entire existing `config/auth.php` `thaid` block (current lines 31-39, which **hardcode `'enabled' => true, 'mock' => true`** — a production-bypass landmine: those literals would make the feature/mock appear enabled with no credentials). Do NOT extend — wholesale-replace so no legacy `enabled`/`mock` literal survives. Then create `src/Services/ThaIdConfig.php`.
- **CRITICAL (review C1)**: both `mock` and any `enabled` notion must default to **false** and come from env only. `ThaIdConfig` must read the NEW keys; there is no `'enabled'` literal in config — enabled is *derived* (see below). Grep the codebase for `['thaid']['mock']` / `['thaid']['enabled']` and ensure the only readers are `ThaIdConfig` (the legacy `App\Controllers\AuthController::thaidLogin` reader is deleted in Task 8).
- **IMPLEMENT** (config): 
  ```php
  'thaid' => [
    'mock'          => filter_var($_ENV['THAID_MOCK'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'client_id'     => $_ENV['THAID_CLIENT_ID'] ?? '',
    'client_secret' => $_ENV['THAID_CLIENT_SECRET'] ?? '',
    'redirect_uri'  => $_ENV['THAID_REDIRECT_URI'] ?? '',
    'authorize_url' => $_ENV['THAID_AUTHORIZE_URL'] ?? 'https://imauth.bora.dopa.go.th/api/v2/oauth2/auth/',
    'token_url'     => $_ENV['THAID_TOKEN_URL']     ?? 'https://imauth.bora.dopa.go.th/api/v2/oauth2/token/',
    'userinfo_url'  => $_ENV['THAID_USERINFO_URL']  ?? 'https://imauth.bora.dopa.go.th/api/v2/oauth2/user/',
    'scope'         => $_ENV['THAID_SCOPE'] ?? 'pid name',
    'pkce'          => filter_var($_ENV['THAID_PKCE'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'client_auth'   => $_ENV['THAID_CLIENT_AUTH'] ?? 'basic', // basic | post
  ],
  ```
  `ThaIdConfig`: constructor loads the array (allow injection of the array for tests). `hasCredentials()` = client_id && client_secret && redirect_uri all non-empty. `isMock()` = `mock`. `isRealEnabled()` = `hasCredentials()`. `isEnabled()` = `isRealEnabled() || (isMock() && !isProd())`. `isProd()` = `($_ENV['APP_ENV'] ?? 'production')==='production'`. Plus typed getters.
  - **(review H2-sec)** In the constructor, if `pkce` is **false** AND `isRealEnabled()`, emit `error_log('[thaid] WARNING: PKCE disabled — code-interception protection off; do not run this way in production')`. PKCE-off is a hedge for a non-conformant provider, never a default.
- **MIRROR**: `config/api.php` env+default style; `final class` service.
- **GOTCHA**: production + mock-only (no creds) ⇒ `isEnabled()` false. Never enable the mock in prod (this preserves the intent of the now-deleted legacy `AuthController::thaidLogin` prod block).
- **VALIDATE**: `ThaIdConfigTest` cases below.

### Task 4: Identity DTO + provider adapter
- **ACTION**: Create `src/Dtos/ThaIdIdentityDto.php` and `src/Services/ThaIdProvider.php`.
- **IMPLEMENT**: `ThaIdIdentityDto` = `final`, `readonly` `{string $sub, string $nameTh, string $email, bool $emailVerified}` + `static fromUserInfo(array $json, array $fieldMap): self` reading configurable keys (defaults: sub←`sub`/`pid`, name←`name`/`fullname`, email←`email`, emailVerified←`email_verified` cast to bool, **default false** when the claim is absent). `ThaIdProvider(private HttpClientInterface $http, private ThaIdConfig $cfg)`:
  - `authorizeUrl(string $state, ?string $codeChallenge): string` → `authorize_url` + `http_build_query(['response_type'=>'code','client_id'=>..,'redirect_uri'=>..,'scope'=>..,'state'=>$state] + pkce?['code_challenge'=>$codeChallenge,'code_challenge_method'=>'S256'])`.
  - `exchangeCode(string $code, ?string $codeVerifier): string` → POST `token_url` form `grant_type=authorization_code,code,redirect_uri` (+`code_verifier` if pkce); client auth via Basic header (`client_auth==='basic'`) or `client_id/client_secret` in body (`post`); parse JSON, return `access_token`; throw `\RuntimeException` if status≠200 or no token.
  - `fetchUserInfo(string $accessToken): ThaIdIdentityDto` → GET `userinfo_url` with `Authorization: Bearer`, parse JSON → DTO; throw on non-200 / missing `sub`.
- **MIRROR**: DTO style `src/Dtos/LoginRequestDto.php`; service ctor-injection `src/Services/*`.
- **GOTCHA**: DOPA field names are the one unknown — keep them in the field map sourced from config so a wrong guess is a config edit, not a code change. Treat empty `sub` as fatal (can't key a user).
- **GOTCHA (review M3-sec)**: thrown exceptions must carry a **generic** message tied to the HTTP status only — e.g. `throw new \RuntimeException("token_exchange_failed: HTTP {$resp->status}")` — **never** embed the raw DOPA response body (it may contain `error_description` / credential hints that would then reach `error_log` in the controller's catch).
- **VALIDATE**: `ThaIdProviderTest` with `FakeHttpClient`.

### Task 5: User model link
- **ACTION**: Add `User::findByThaidSub(string $sub): ?array`.
- **IMPLEMENT**: `Database::queryOne("SELECT * FROM users WHERE thaid_sub = ?", [$sub])`. `User::create()` already passes arbitrary columns through, so `create([...])` works once the column exists.
- **MIRROR**: `src/Models/User.php:30-36` (`findByEmail`).
- **GOTCHA (review M1-arch)**: the `users` schema (`hr_budget_only.sql:1698-1713`) has these **NOT-NULL-without-default** columns: `email`, `password`, `name`. `role` defaults `'viewer'`, `is_active` defaults `1`. So the create payload MUST include all three: `create(['email'=>$synthOrReal,'password'=>$randomHash,'name'=>$identity->nameTh ?: $identity->sub,'role'=>'viewer','thaid_sub'=>$identity->sub])`. The SQLite test `CREATE TABLE users` MUST mirror these `NOT NULL` constraints (SQLite is permissive — without them the "password non-empty" assertion passes vacuously).
- **GOTCHA**: `User::create()` hashes a `password` key — but here pass an **already-random** secret so ThaID-only accounts satisfy the NOT-NULL `password` and can never be password-logged-in: `bin2hex(random_bytes(16))` (User::create will hash it). Mirrors `mockThaIDLogin` line 269.
- **VALIDATE**: covered by `ThaIdAuthServiceTest`.

### Task 6: Orchestration service
- **ACTION**: Create `src/Services/ThaIdAuthService.php`.
- **IMPLEMENT**:
  - `beginLogin(): array` → generate `state=bin2hex(random_bytes(16))`; if pkce: `verifier=rtrim(strtr(base64_encode(random_bytes(32)),'+/','-_'),'=')`, `challenge=rtrim(strtr(base64_encode(hash('sha256',$verifier,true)),'+/','-_'),'=')`. Return `['url'=>provider->authorizeUrl(state, challenge), 'state'=>$state, 'code_verifier'=>$verifier]`. (Controller stores state/verifier in `$_SESSION` — service stays I/O-free for testability.)
  - `completeLogin(string $code, string $returnedState, string $expectedState, ?string $codeVerifier): array` → if `$returnedState===''||!hash_equals($expectedState,$returnedState)` throw `\RuntimeException('state_mismatch')`; `$access=provider->exchangeCode($code,$codeVerifier)`; `$identity=provider->fetchUserInfo($access)`; `resolveUser($identity)`; return the user array (caller mints cookie+session).
  - `private resolveUser(ThaIdIdentityDto $id): array` →
    1. `$u = User::findByThaidSub($id->sub)` — if found, that's the user (primary key).
    2. **(review H1-sec — account-takeover gate)** Only attempt email-linking when the email is **provably verified by DOPA**: `if ($u===null && $id->email!=='' && $id->emailVerified) { $byEmail = User::findByEmail($id->email); if ($byEmail) { User::update($byEmail['id'], ['thaid_sub'=>$id->sub]); $u = $byEmail; } }`. When `emailVerified` is false/absent, **do NOT link by email** — fall through to create. This prevents an attacker who can set an arbitrary (unverified) email at the IdP from hijacking an existing local account.
    3. If still null → create a new **viewer** (Task 5 payload).
    4. Re-`User::find()` the id, reject if `is_active===0` (throw `'inactive'`).
- **MIRROR**: `AuthService::authenticate` flow + `Auth::mockThaIDLogin` find-or-create (lines 248-279).
- **GOTCHA**: use `hash_equals` for state (timing-safe). New users → role `'viewer'` only. Email may be empty/unverified → synthesize `"{$id->sub}@thaid.local"` for the unique `email` column; this is a **schema placeholder only** — `thaid_sub` is the canonical identity for ThaID accounts, never the synthetic email (review L3).
- **VALIDATE**: `ThaIdAuthServiceTest` (SQLite).

### Task 7: Controller (status / login / callback)
- **ACTION**: Create `src/Api/Controllers/ThaIdController.php`; wire its real `HttpClient` + provider + service **and `ThaIdConfig`** in the constructor (default-arg DI like other controllers). All four are constructor params with sensible defaults so a test can inject a config-only stub for `status()`: `__construct(private ThaIdConfig $cfg = new ThaIdConfig(), private ThaIdAuthService $service = new ThaIdAuthService())` (the service internally builds the real provider+CurlHttpClient).
- **IMPLEMENT**:
  - `status()`: `CorsMiddleware::apply();` then `ApiResponse::ok(['enabled'=>$cfg->isEnabled(),'mock'=>$cfg->isMock() && !$cfg->isProd()])`. No auth (login-gate info).
  - `login()`: **(review M2-arch)** if `!$cfg->isEnabled()` → `Router::redirect('/')` and return (a top-level navigation gets a redirect, NOT a 403+body; the JSON-shaped 403 is reserved for the XHR `status` surface). If mock-mode (and not prod): reuse `Auth::mockThaIDLogin()` → user, then `AuthCookie::set(Jwt::issue(...))` + `Auth::login($user)`, `Router::redirect('/')`. Else real: `$b=service->beginLogin(); $_SESSION['thaid_oauth']=['state'=>$b['state'],'code_verifier'=>$b['code_verifier'],'ts'=>time()];` then `header("Location: {$b['url']}")`, exit (302).
  - `callback()` — **explicit ordering (review C2/M1-sec/H3-arch)**:
    1. **First**, handle provider errors: `if (!empty($_GET['error'])) { error_log('[thaid] auth_error code='.preg_replace('/[^a-z_]/','',(string)$_GET['error'])); $_SESSION['flash_error']='เข้าสู่ระบบด้วย ThaID ไม่สำเร็จ'; Router::redirect('/'); return; }` — log the sanitized error **code only**, never `error_description` (PII risk).
    2. Read `$sess = $_SESSION['thaid_oauth'] ?? null` and **immediately `unset($_SESSION['thaid_oauth'])` BEFORE any other call** (single-use, atomic with the read; survives even if a later step throws). Reject if `$sess===null` or `time()-$sess['ts'] > 600`.
    3. `try { $user = service->completeLogin((string)($_GET['code']??''), (string)($_GET['state']??''), $sess['state'], $sess['code_verifier']); AuthCookie::set(Jwt::issue((int)$user['id'],['email'=>$user['email'],'role'=>$user['role']]), time()+$jwtTtl); Auth::login($user); /* session_regenerate_id happens here — AFTER oauth state already consumed */ User::updateLastLogin((int)$user['id']); error_log("[thaid] login_success user_id={$user['id']}"); Router::redirect('/'); } catch(\Throwable $e){ error_log('[thaid] login_failed: '.$e->getMessage()); $_SESSION['flash_error']='เข้าสู่ระบบด้วย ThaID ไม่สำเร็จ'; Router::redirect('/'); }`.
- **MIRROR**: cookie mint `AuthController.php:79-101`→`AuthCookie`; session `Auth::login`; controller try/catch+error_log style.
- **GOTCHA (review H3-arch)**: the OAuth `state`/`verifier` live in `$_SESSION`, which only survives the **DOPA→callback top-level GET** because `config/app.php` session cookie is **`SameSite=Lax`** (verified: `config/app.php:23`). A Lax cookie IS sent on top-level GET navigations; a `Strict` one would NOT be, silently breaking state validation on every real login. **Do not change the session cookie to `Strict`** without first moving the OAuth state into a dedicated `SameSite=Lax` cookie. Add an inline code comment stating this dependency.
- **GOTCHA**: `callback()` redirects **only** to the fixed `/` (no user-controlled redirect → no open redirect). Never echo `$code`/`$state`/tokens. CORS not needed on login/callback (top-level navigations, not XHR) — only `status()` applies CORS. Mock path must also be blocked in prod via `isEnabled()`.
- **VALIDATE**: manual smoke (mock mode) + `status()` asserted in e2e; service/provider carry the unit coverage.

### Task 8: Routes
- **ACTION**: Edit `routes/web.php`.
- **IMPLEMENT**: add `use App\Api\Controllers\ThaIdController as ApiThaIdController;`. After line 44 (`/auth/me`):
  ```php
  Router::get('/api/v1/auth/thaid/status',   [ApiThaIdController::class, 'status']);
  Router::get('/api/v1/auth/thaid/login',    [ApiThaIdController::class, 'login']);
  Router::get('/api/v1/auth/thaid/callback', [ApiThaIdController::class, 'callback']);
  ```
  Replace legacy `/thaid/login` (line 174) handler with a 302 to the new endpoint: `Router::get('/thaid/login', fn() => Router::redirect('/api/v1/auth/thaid/login'));` and remove the `use App\Controllers\AuthController;` import (line 12).
- **(review H2-arch — finish dead-code consolidation)**: once `/thaid/login` is a 302 stub, `App\Controllers\AuthController` has **no remaining caller** (its only method was `thaidLogin`, plus a private `logActivity`). **DELETE the file `src/Controllers/AuthController.php`** (`git rm`). Recoverable from the `pre-spa-cutover` tag if ever needed. This also removes the stale `$authConfig['thaid']['mock']` reader (line 32 of that file) flagged in C1. Verify no other `use App\Controllers\AuthController` references remain (grep).
- **MIRROR**: existing route registration block.
- **GOTCHA**: keep these BEFORE the `notFound()` catch-all (they're explicit routes, so fine). `status` is under `/api/` → an unauth miss still returns JSON via the controller, not the SPA shell. `Auth::mockThaIDLogin()` STAYS (the new `login()` mock path uses it) — only the web `AuthController` wrapper is deleted.
- **VALIDATE**: `tests/e2e/api/thaid-status.spec.ts` hits the live route; `grep -r "App\\Controllers\\AuthController"` returns nothing.

### Task 9: Migration + seed
- **ACTION**: Create `database/migrations/065_add_thaid_sub_to_users.sql`; edit `database/hr_budget_only.sql`.
- **IMPLEMENT**: migration `ALTER TABLE \`users\` ADD COLUMN \`thaid_sub\` VARCHAR(64) NULL DEFAULT NULL, ADD UNIQUE KEY \`uniq_thaid_sub\` (\`thaid_sub\`);`. In the seed, add `\`thaid_sub\` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,` to the `users` CREATE (after `department`) and `UNIQUE KEY \`uniq_thaid_sub\` (\`thaid_sub\`)`.
- **GOTCHA**: `database/migrations/*.sql` are git-ignored / local-only — the **seed edit is what CI actually applies** to `hr_budget_test`. A UNIQUE index on a nullable column allows many NULLs in MySQL (good). SQLite test harness creates its own `users` table, so add `thaid_sub` there too.
- **VALIDATE**: run migration locally if MySQL up; CI green proves seed parity.

### Task 10: Frontend status + button
- **ACTION**: Update `frontend/src/types/api.ts`, create `frontend/src/api/auth.ts`, update `LoginPage.vue`.
- **IMPLEMENT**: `ThaidStatus { enabled: boolean; mock: boolean }`. `fetchThaidStatus(): Promise<ThaidStatus>` (GET `/api/v1/auth/thaid/status`, `X-Requested-With`, same-origin; default `{enabled:false,mock:false}` on any error). `thaidLoginUrl()` = **(review M2-sec — normalize trailing slash)** `((import.meta.env.VITE_API_BASE_URL || '').replace(/\/$/, '')) + '/api/v1/auth/thaid/login'` so a base with a trailing `/` doesn't produce `//api/...` (which could make `redirect_uri` mismatch DOPA's registered value). In `LoginPage.vue`: `onMounted` fetch status into a ref; when `enabled`, render a divider "หรือ" + a secondary `Button`/anchor with the ThaID glyph whose click sets `window.location.href = thaidLoginUrl()` (full-page redirect, NOT fetch). Hide entirely when disabled.
- **MIRROR**: download-URL env pattern from `frontend/src/api/budgetExecution.ts` (this branch's prior work); fetch+error-default from `stores/auth.ts:bootstrap`.
- **GOTCHA**: must be a real navigation (`window.location`) — OAuth cannot run inside `fetch()`. Keep the email/password form fully intact; ThaID is additive.
- **VALIDATE**: `cd frontend && npx vue-tsc -b` (0 errors); CI build + deploy build.

### Task 11: Tests
- **ACTION**: Write the 3 unit tests + 1 e2e (details in Testing Strategy).
- **VALIDATE**: `vendor/bin/phpunit --testsuite Unit` all green; e2e in api suite green.

### Task 12: Rebuild SPA artifact
- **ACTION**: `cd frontend; $env:VITE_BASE='/hr_budget/public/app/'; npm run build` → commit `public/app/`.
- **GOTCHA**: do NOT commit `frontend/dist`; only `public/app/`. Verify `database/hr_budget.sql` stays unstaged.
- **VALIDATE**: `git status` shows only intended files.

---

## Testing Strategy

### Unit Tests

| Test | Input | Expected | Edge? |
|---|---|---|---|
| `ThaIdConfig::isEnabled` real | creds set, mock off | true | |
| `ThaIdConfig::isEnabled` dormant | no creds, mock off | false | ✓ default-off |
| `ThaIdConfig::isEnabled` mock dev | no creds, mock on, env≠prod | true | |
| `ThaIdConfig::isEnabled` mock prod | mock on, env=prod | false | ✓ prod guard |
| `ThaIdProvider::authorizeUrl` | state, challenge | query has client_id/redirect_uri/response_type=code/scope/state/code_challenge | |
| `ThaIdProvider::exchangeCode` ok | FakeHttp 200 `{access_token}` | returns token; Basic header present | |
| `ThaIdProvider::exchangeCode` fail | FakeHttp 400 | throws RuntimeException | ✓ |
| `ThaIdProvider::fetchUserInfo` | FakeHttp 200 `{sub,name,email}` | DTO populated | |
| `ThaIdProvider::fetchUserInfo` no sub | FakeHttp 200 `{}` | throws | ✓ |
| `ThaIdAuthService::completeLogin` state mismatch | wrong state | throws (no HTTP call) | ✓ CSRF |
| `ThaIdAuthService` new identity | unknown sub | creates **viewer**, returns user, password hashed & non-empty | |
| `ThaIdAuthService` returning user | known thaid_sub | returns same user id (no dup) | |
| `ThaIdAuthService` link by email | email matches existing, sub null | back-fills thaid_sub, returns that user | ✓ |
| `ThaIdAuthService` inactive | found user is_active=0 | throws inactive | ✓ |
| `ThaIdController::status` enabled | config with creds (injected) | `ApiResponse::$lastBody` = `{success:true,data:{enabled:true,mock:false}}` | |
| `ThaIdController::status` dormant | empty config (injected) | `{enabled:false,mock:false}` | ✓ default-off |

### Edge Cases Checklist
- [x] Missing/empty config (dormant) — `isEnabled=false`, e2e asserts it
- [x] State mismatch (CSRF) — throws before any token exchange
- [x] Token endpoint error / network (status 0) — throws → callback flash-errors, redirect `/`
- [x] Userinfo missing `sub` — throws
- [x] Inactive user — refused
- [x] New vs returning vs email-linked user — all covered
- [ ] Concurrent logins — N/A (stateless per-request; session per-browser)

---

## Validation Commands

### Static Analysis
```bash
php -l src/Services/ThaIdAuthService.php   # + each new PHP file
cd frontend && npx vue-tsc -b              # EXPECT: 0 errors
```

### Unit Tests
```bash
vendor/bin/phpunit --testsuite Unit        # EXPECT: all green incl. new ThaId* tests
```

### Build
```bash
cd frontend && npm run build                                   # CI artifact, base '/'
cd frontend && VITE_BASE=/hr_budget/public/app/ npm run build  # deploy → public/app/
```

### E2E (api suite — what CI runs)
```bash
BASE_URL=http://localhost:5174 API_URL=http://127.0.0.1:8000 npx playwright test tests/e2e/api/thaid-status.spec.ts
```
EXPECT: `{ enabled:false }` in the unconfigured test env.

### Manual Validation (mock mode, dev)
- [ ] `THAID_MOCK=true APP_ENV=local` → SPA login shows ThaID button → click → lands on `/dashboard`, `/auth/me` returns the mock user, legacy `/budgets` also loads (session present)
- [ ] Unset all THAID_* → button hidden; `GET /api/v1/auth/thaid/login` → 403/redirect

---

## Acceptance Criteria
- [ ] Dormant by default: no creds ⇒ status `enabled:false`, button hidden, real `login` refuses
- [ ] Configured: full authorize→callback→cookie+session→SPA `/` flow works (mock-verified; real-verified by admin post-deploy)
- [ ] New ThaID users created as `viewer`; returning users de-duplicated by `thaid_sub`
- [ ] All new unit tests + existing suite green; `vue-tsc` 0 errors; both builds succeed
- [ ] No secrets/tokens logged; state validated with `hash_equals`; callback redirect fixed to `/`

## Completion Checklist
- [ ] Patterns mirrored (service/DTO/controller/cookie/test)
- [ ] Error handling = try/catch + `error_log`, uniform user-facing flash
- [ ] No hardcoded secrets (all env via `config/auth.php`)
- [ ] `database/hr_budget.sql` never staged; only `public/app/` build committed
- [ ] Self-contained — no further codebase search needed

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| DOPA userinfo field names differ from guess | High | Med | All field names in config field-map; adapter-isolated; document in `.env.example` |
| DOPA rejects PKCE / unknown params | Low | Med | `THAID_PKCE` toggle (default on); `client_auth` basic/post toggle |
| Token-endpoint client-auth style (basic vs post) | Med | Med | `THAID_CLIENT_AUTH` config; default `basic` (DOPA norm) |
| Legacy session pages break for ThaID users | Low | Med | Callback mints **session too** (`Auth::login`) — superset of old behavior |
| Cookie helper refactor regresses password login | Low | High | Extract verbatim; run existing auth tests before commit |
| Real flow unverifiable in CI | Certain | Low | Mock + unit coverage; explicit manual post-deploy checklist + `.env.example` docs |

## Notes
- **One deliberate unknown**: exact DOPA OIDC response shape. The `ThaIdProvider` + config field-map quarantine it; correcting it is a `.env`/config change, not a code change. Everything else reuses proven internal patterns (JWT cookie, ApiResponse, SQLite test harness, layered services).
- **Why session + JWT both**: the SPA needs JWT; legacy `/budgets`,`/files` still use PHP session. Minting both makes ThaID a strict superset of today's mock and avoids a regression while those legacy pages await retirement.
- **Add to `.env.example`** (if present) the full `THAID_*` block with comments so an admin knows exactly what to set to flip the feature on.
- **Confidence**: 7/10 single-pass for everything except the live DOPA field mapping (which no plan can verify without creds).
```