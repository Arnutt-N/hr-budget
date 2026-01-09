<?php
/**
 * Authentication Class
 * 
 * Handles user authentication and session management
 */

namespace App\Core;

use App\Models\User;

class Auth
{
    private static ?array $user = null;
    private static array $config = [];

    /**
     * Initialize auth configuration
     */
    public static function init(): void
    {
        if (empty(self::$config)) {
            self::$config = require __DIR__ . '/../../config/auth.php';
        }

        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            $appConfig = require __DIR__ . '/../../config/app.php';
            
            session_set_cookie_params([
                'lifetime' => $appConfig['session']['lifetime'] * 60,
                'path' => '/',
                'secure' => $appConfig['session']['secure'],
                'httponly' => $appConfig['session']['http_only'],
                'samesite' => $appConfig['session']['same_site']
            ]);
            
            session_start();
        }

        // Load user from session
        self::loadUserFromSession();
    }

    /**
     * Load user data from session
     */
    private static function loadUserFromSession(): void
    {
        $sessionKey = self::$config['session']['key'] ?? 'hr_budget_user';
        
        if (isset($_SESSION[$sessionKey])) {
            self::$user = $_SESSION[$sessionKey];
        }
    }

    /**
     * Attempt to login with email and password
     */
    public static function attempt(string $email, string $password): bool
    {
        // Validate email domain
        $domain = substr(strrchr($email, "@"), 1);
        $allowedDomains = self::$config['allowed_domains'] ?? [];
        
        if (!empty($allowedDomains) && !in_array($domain, $allowedDomains)) {
            return false;
        }

        // Find user by email
        $user = User::findByEmail($email);
        
        if (!$user) {
            return false;
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // Check if user is active
        if (isset($user['is_active']) && !$user['is_active']) {
            return false;
        }

        // Login successful
        self::login($user);
        
        // Update last login
        User::updateLastLogin($user['id']);
        
        return true;
    }

    /**
     * Login user (set session)
     */
    public static function login(array $user): void
    {
        // Remove password from session data
        unset($user['password']);
        
        self::$user = $user;
        
        $sessionKey = self::$config['session']['key'] ?? 'hr_budget_user';
        $_SESSION[$sessionKey] = $user;
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    /**
     * Logout user
     */
    public static function logout(): void
    {
        self::$user = null;
        
        $sessionKey = self::$config['session']['key'] ?? 'hr_budget_user';
        unset($_SESSION[$sessionKey]);
        
        // Destroy session
        session_destroy();
    }

    /**
     * Check if user is logged in
     */
    public static function check(): bool
    {
        return self::$user !== null;
    }

    /**
     * Check if user is guest
     */
    public static function guest(): bool
    {
        return !self::check();
    }

    /**
     * Get current user
     */
    public static function user(): ?array
    {
        return self::$user;
    }

    /**
     * Get current user ID
     */
    public static function id(): ?int
    {
        return self::$user['id'] ?? null;
    }

    /**
     * Check if user has role
     */
    public static function hasRole(string $role): bool
    {
        if (!self::check()) {
            return false;
        }
        
        return (self::$user['role'] ?? '') === $role;
    }

    /**
     * Check if user has permission
     */
    public static function can(string $permission): bool
    {
        if (!self::check()) {
            return false;
        }

        $userRole = self::$user['role'] ?? 'viewer';
        $roleConfig = self::$config['roles'][$userRole] ?? [];
        $permissions = $roleConfig['permissions'] ?? [];

        // Super admin has all permissions
        if (in_array('*', $permissions)) {
            return true;
        }

        // Check exact permission
        if (in_array($permission, $permissions)) {
            return true;
        }

        // Check wildcard permission (e.g., 'budgets.*')
        $parts = explode('.', $permission);
        if (count($parts) >= 2) {
            $wildcardPermission = $parts[0] . '.*';
            if (in_array($wildcardPermission, $permissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Require authentication (redirect if not logged in)
     */
    public static function require(): void
    {
        if (self::guest()) {
            Router::redirect('/login');
        }
    }

    /**
     * Require specific role
     */
    public static function requireRole(string $role): void
    {
        self::require();
        
        if (!self::hasRole($role)) {
            http_response_code(403);
            View::render('errors/403');
            exit;
        }
    }

    /**
     * Require specific permission
     */
    public static function requirePermission(string $permission): void
    {
        self::require();
        
        if (!self::can($permission)) {
            http_response_code(403);
            View::render('errors/403');
            exit;
        }
    }

    /**
     * Mock ThaID login (for development)
     */
    public static function mockThaIDLogin(): array
    {
        $mockUser = [
            'id' => 0,
            'email' => 'thaid.user@moj.go.th',
            'name' => 'ผู้ใช้ ThaID (Mock)',
            'role' => 'viewer',
            'department' => 'กระทรวงยุติธรรม'
        ];

        // Check if user exists, if not create
        $existingUser = User::findByEmail($mockUser['email']);
        
        if ($existingUser) {
            self::login($existingUser);
            return $existingUser;
        }

        // Create new user
        $userId = User::create([
            'email' => $mockUser['email'],
            'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'name' => $mockUser['name'],
            'role' => $mockUser['role'],
            'department' => $mockUser['department']
        ]);

        $newUser = User::find($userId);
        self::login($newUser);
        
        return $newUser;
    }
}
