<?php
/**
 * Authentication Controller
 * 
 * Handles login, logout, and password reset
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Router;

class AuthController
{
    /**
     * Mock ThaID login.
     *
     * Phase 6 SPA cutover: web session login (showLogin/login/logout) and
     * forgot-password were retired — primary auth is the Vue SPA + the JWT
     * cookie at /api/v1/auth/*. ThaID has no SPA flow yet, so this server
     * route is kept as a documented parity-gap remnant. It mints a session
     * via Auth::mockThaIDLogin() and lands on the SPA shell at '/'.
     * (Recover the removed methods from the `pre-spa-cutover` git tag.)
     */
    public function thaidLogin(): void
    {
        // Security: the mock ThaID flow authenticates ANY caller as a viewer
        // with no real identity proof. It must never run in production. Gate it
        // on APP_ENV so only dev/testing can use the mock.
        $appEnv = $_ENV['APP_ENV'] ?? 'production';
        $authConfig = require __DIR__ . '/../../config/auth.php';
        $isMock = $authConfig['thaid']['mock'] ?? false;

        if ($appEnv === 'production' && $isMock) {
            $_SESSION['flash_error'] = 'การเข้าสู่ระบบผ่าน ThaID (Mock) ถูกปิดใช้งานในระบบจริง';
            Router::redirect('/');
            return;
        }

        try {
            $user = Auth::mockThaIDLogin();
            $this->logActivity('login', 'User logged in via ThaID (Mock)');
            Router::redirect('/');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'ไม่สามารถเข้าสู่ระบบผ่าน ThaID ได้';
            Router::redirect('/');
        }
    }

    /**
     * Log user activity
     */
    private function logActivity(string $action, string $details): void
    {
        try {
            \App\Core\Database::insert('activity_logs', [
                'user_id' => Auth::id() ?? '0',
                'action' => $action,
                'details' => $details,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (\Exception $e) {
            // Silently fail - don't break the app if logging fails
        }
    }
}
