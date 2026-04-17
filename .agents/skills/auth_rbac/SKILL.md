---
name: auth_rbac
description: Guide for Authentication and Role-Based Access Control (RBAC) in the HR Budget project.
---

# Authentication & RBAC Guide

Comprehensive guide for managing user authentication and role-based permissions.

## 📑 Table of Contents

- [Authentication Overview](#-authentication-overview)
- [Role Hierarchy](#-role-hierarchy)
- [Permission System](#-permission-system)
- [Implementation Patterns](#-implementation-patterns)
- [Security Best Practices](#-security-best-practices)

## 🔐 Authentication Overview

### Session-Based Authentication

```php
// Login flow
public function login(Request $request): void
{
    $email = $request->post('email');
    $password = $request->post('password');
    
    $user = User::findByEmail($email);
    
    if (!$user || !password_verify($password, $user['password'])) {
        // Rate limiting check
        $this->incrementLoginAttempts($email);
        $this->error('อีเมลหรือรหัสผ่านไม่ถูกต้อง');
        return;
    }
    
    // Regenerate session ID (prevent fixation)
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['org_id'] = $user['organization_id'];
    $_SESSION['login_time'] = time();
    
    Router::redirect('/dashboard');
}
```

### Auth Helper Class

```php
// src/Core/Auth.php
class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }
    
    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    public static function user(): ?array
    {
        if (!self::check()) return null;
        return User::find(self::id());
    }
    
    public static function role(): ?string
    {
        return $_SESSION['role'] ?? null;
    }
    
    public static function orgId(): ?int
    {
        return $_SESSION['org_id'] ?? null;
    }
    
    public static function hasRole(string $role): bool
    {
        return self::role() === $role;
    }
    
    public static function hasAnyRole(array $roles): bool
    {
        return in_array(self::role(), $roles);
    }
}
```

## 👥 Role Hierarchy

### Role Definitions

| Role | Level | Description | Access Scope |
|:-----|:-----:|:------------|:-------------|
| `super_admin` | 1 | ผู้ดูแลระบบสูงสุด | ทุกหน่วยงาน |
| `admin` | 2 | ผู้ดูแลระบบ | ทุกหน่วยงาน |
| `director` | 3 | ผู้อำนวยการกอง | เฉพาะกองของตน |
| `division_head` | 4 | หัวหน้าฝ่าย | เฉพาะฝ่ายของตน |
| `staff` | 5 | เจ้าหน้าที่ | เฉพาะข้อมูลตนเอง |

### Role Hierarchy Check

```php
class Role
{
    private const HIERARCHY = [
        'super_admin' => 1,
        'admin' => 2,
        'director' => 3,
        'division_head' => 4,
        'staff' => 5
    ];
    
    public static function isHigherOrEqual(string $userRole, string $requiredRole): bool
    {
        $userLevel = self::HIERARCHY[$userRole] ?? 999;
        $requiredLevel = self::HIERARCHY[$requiredRole] ?? 0;
        return $userLevel <= $requiredLevel;
    }
}

// Usage
if (Role::isHigherOrEqual(Auth::role(), 'director')) {
    // Can access director-level features
}
```

## 🔑 Permission System

### Permission Matrix

| Permission | super_admin | admin | director | division_head | staff |
|:-----------|:-----------:|:-----:|:--------:|:-------------:|:-----:|
| `users.manage` | ✅ | ✅ | ❌ | ❌ | ❌ |
| `budgets.create` | ✅ | ✅ | ✅ | ✅ | ❌ |
| `budgets.approve` | ✅ | ✅ | ✅ | ❌ | ❌ |
| `budgets.view_all` | ✅ | ✅ | ❌ | ❌ | ❌ |
| `requests.create` | ✅ | ✅ | ✅ | ✅ | ✅ |
| `requests.approve` | ✅ | ✅ | ✅ | ✅ | ❌ |
| `reports.export` | ✅ | ✅ | ✅ | ✅ | ❌ |
| `settings.manage` | ✅ | ❌ | ❌ | ❌ | ❌ |

### Permission Check

```php
class Permission
{
    private const ROLE_PERMISSIONS = [
        'super_admin' => ['*'], // All permissions
        'admin' => [
            'users.manage', 'budgets.*', 'requests.*', 'reports.*'
        ],
        'director' => [
            'budgets.create', 'budgets.approve', 'budgets.view_org',
            'requests.*', 'reports.export'
        ],
        'division_head' => [
            'budgets.create', 'budgets.view_division',
            'requests.create', 'requests.approve', 'reports.export'
        ],
        'staff' => [
            'requests.create', 'budgets.view_own'
        ]
    ];
    
    public static function can(string $permission): bool
    {
        $role = Auth::role();
        if (!$role) return false;
        
        $permissions = self::ROLE_PERMISSIONS[$role] ?? [];
        
        // Check wildcard
        if (in_array('*', $permissions)) return true;
        
        // Check exact match
        if (in_array($permission, $permissions)) return true;
        
        // Check category wildcard (e.g., 'budgets.*')
        $category = explode('.', $permission)[0];
        if (in_array($category . '.*', $permissions)) return true;
        
        return false;
    }
}
```

## 🛠️ Implementation Patterns

### Middleware Pattern

```php
// Require authentication
class AuthMiddleware
{
    public function handle(): bool
    {
        if (!Auth::check()) {
            Router::redirect('/login');
            return false;
        }
        return true;
    }
}

// Require specific role
class RoleMiddleware
{
    public function handle(string $requiredRole): bool
    {
        if (!Role::isHigherOrEqual(Auth::role(), $requiredRole)) {
            http_response_code(403);
            View::render('errors/403');
            return false;
        }
        return true;
    }
}
```

### Controller Authorization

```php
class BudgetController extends Controller
{
    public function approve(int $id): void
    {
        // Check permission
        if (!Permission::can('budgets.approve')) {
            $this->forbidden('ไม่มีสิทธิ์อนุมัติงบประมาณ');
            return;
        }
        
        // Check organization scope
        $budget = Budget::find($id);
        if (!$this->canAccessOrg($budget['organization_id'])) {
            $this->forbidden('ไม่มีสิทธิ์เข้าถึงข้อมูลหน่วยงานนี้');
            return;
        }
        
        // Proceed with approval
        Budget::approve($id, Auth::id());
    }
    
    private function canAccessOrg(int $orgId): bool
    {
        // Super admin/admin can access all
        if (Auth::hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }
        
        // Others can only access their org
        return Auth::orgId() === $orgId;
    }
}
```

### View Authorization

```php
<!-- Show button only if user has permission -->
<?php if (Permission::can('budgets.create')): ?>
    <a href="<?= View::url('/budgets/create') ?>" class="btn-primary">
        สร้างงบประมาณ
    </a>
<?php endif; ?>

<!-- Show content based on role -->
<?php if (Auth::hasAnyRole(['super_admin', 'admin'])): ?>
    <div class="admin-panel">
        <!-- Admin-only content -->
    </div>
<?php endif; ?>
```

## 🔒 Security Best Practices

### Session Configuration

```php
// In bootstrap or config
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // HTTPS only
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 3600); // 1 hour
```

### Password Requirements

```php
class PasswordValidator
{
    public static function validate(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'ต้องมีตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'ต้องมีตัวพิมพ์เล็กอย่างน้อย 1 ตัว';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'ต้องมีตัวเลขอย่างน้อย 1 ตัว';
        }
        
        return $errors;
    }
}
```

### Rate Limiting

```php
class RateLimiter
{
    public static function tooManyAttempts(string $key, int $maxAttempts = 5): bool
    {
        $attempts = $_SESSION['rate_limit'][$key] ?? ['count' => 0, 'reset' => time() + 900];
        
        if (time() > $attempts['reset']) {
            $_SESSION['rate_limit'][$key] = ['count' => 0, 'reset' => time() + 900];
            return false;
        }
        
        return $attempts['count'] >= $maxAttempts;
    }
    
    public static function hit(string $key): void
    {
        if (!isset($_SESSION['rate_limit'][$key])) {
            $_SESSION['rate_limit'][$key] = ['count' => 0, 'reset' => time() + 900];
        }
        $_SESSION['rate_limit'][$key]['count']++;
    }
}
```
