<?php
/**
 * Authentication Controller
 * 
 * Handles login, logout, and password reset
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\User;

class AuthController
{
    /**
     * Show login page
     */
    public function showLogin(): void
    {
        // Redirect if already logged in
        if (Auth::check()) {
            Router::redirect('/');
            return;
        }

        View::setLayout('auth');
        View::render('auth/login', [
            'title' => 'เข้าสู่ระบบ'
        ]);
    }

    /**
     * Handle login attempt
     */
    public function login(): void
    {
        // Verify CSRF
        if (!View::verifyCsrf()) {
            $this->redirectWithError('Invalid request. Please try again.');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate input
        if (empty($email) || empty($password)) {
            $this->redirectWithError('กรุณากรอกอีเมลและรหัสผ่าน');
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithError('รูปแบบอีเมลไม่ถูกต้อง');
            return;
        }

        // Attempt login
        if (Auth::attempt($email, $password)) {
            // Log activity
            $this->logActivity('login', 'User logged in successfully');
            
            // Redirect to dashboard
            Router::redirect('/');
            return;
        }

        $this->redirectWithError('อีเมลหรือรหัสผ่านไม่ถูกต้อง');
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        // Log activity before logout
        if (Auth::check()) {
            $this->logActivity('logout', 'User logged out');
        }

        Auth::logout();
        Router::redirect('/login');
    }

    /**
     * Mock ThaID login
     */
    public function thaidLogin(): void
    {
        try {
            $user = Auth::mockThaIDLogin();
            $this->logActivity('login', 'User logged in via ThaID (Mock)');
            Router::redirect('/');
        } catch (\Exception $e) {
            $this->redirectWithError('ไม่สามารถเข้าสู่ระบบผ่าน ThaID ได้');
        }
    }

    /**
     * Show forgot password page
     */
    public function showForgotPassword(): void
    {
        View::setLayout('auth');
        View::render('auth/forgot-password', [
            'title' => 'ลืมรหัสผ่าน'
        ]);
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(): void
    {
        if (!View::verifyCsrf()) {
            $this->redirectWithError('Invalid request. Please try again.', '/forgot-password');
            return;
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $this->redirectWithError('กรุณากรอกอีเมล', '/forgot-password');
            return;
        }

        // Check if user exists
        $user = User::findByEmail($email);

        // Always show success message for security (don't reveal if email exists)
        $_SESSION['flash_success'] = 'หากอีเมลนี้มีในระบบ คุณจะได้รับลิงก์รีเซ็ตรหัสผ่านทางอีเมล';
        
        if ($user) {
            // TODO: Send password reset email
            $this->logActivity('password_reset_request', "Password reset requested for: {$email}");
        }

        Router::redirect('/forgot-password');
    }

    /**
     * Redirect with error message
     */
    private function redirectWithError(string $message, string $url = '/login'): void
    {
        $_SESSION['flash_error'] = $message;
        Router::redirect($url);
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
