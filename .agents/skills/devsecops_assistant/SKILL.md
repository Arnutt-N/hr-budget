---
name: devsecops_assistant
description: Guide for server configuration, deployment, security hardening, and performance tuning for the HR Budget project.
---

# DevSecOps Assistant

Comprehensive guide for deploying, securing, optimizing, and maintaining the HR Budget application.

## 📑 Table of Contents
- [Server Requirements](#-server-requirements)
- [Environment Configuration](#-environment-configuration)
- [Web Server Setup](#-web-server-setup)
- [Security Hardening](#-security-hardening)
- [Security Deep Dive](#-security-deep-dive)
- [Performance Tuning](#-performance-tuning)
- [Deployment Workflow](#-deployment-workflow)

## 🖥️ Server Requirements

| Component | Requirement | Recommended |
|:----------|:------------|:------------|
| **OS** | Linux / Windows | Ubuntu 22.04 LTS |
| **PHP** | 8.3+ | 8.3 (Latest Stable) |
| **Database** | MySQL 8.0+ | MySQL 8.0 / MariaDB 10.6+ |
| **Web Server**| Apache / Nginx | Nginx (Performance) / Apache (Ease of use) |

**Required Extensions:**
- `pdo_mysql`, `mbstring`, `openssl`, `json`, `curl`, `gd`, `xml`, `zip`, `opcache`

## ⚙️ Environment Configuration

### Production `.env`
```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hrbudget.moj.go.th

DB_HOST=127.0.0.1
DB_DATABASE=hr_budget_prod
DB_USERNAME=secure_user
DB_PASSWORD=complex_password_here

SESSION_SECURE=true
SESSION_LIFETIME=120
```

> ⚠️ **Never commit `.env` to Git!**

## 🌐 Web Server Setup

### Apache (`.htaccess`)
The project comes with a default `.htaccess` in `public/`:
- Enables URL Rewriting
- Sets Security Headers (XSS, Frame-Options)
- Disables Directory Listing

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name hrbudget.moj.go.th;
    root /var/www/hr_budget/public;
    index index.php;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 🔒 Security Hardening

### 1. File Permissions
```bash
chown -R www-data:www-data /var/www/hr_budget
find /var/www/hr_budget -type f -exec chmod 644 {} \;
find /var/www/hr_budget -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
```

### 2. Disable Sensitive Files
Ensure web server blocks access to:
- `.env`, `.git/`, `composer.json`, `package.json`

---

## 🛡️ Security Deep Dive

### OWASP Top 10 Checklist

| Vulnerability | Mitigation | Status |
|:--------------|:-----------|:------:|
| **SQL Injection** | Use prepared statements (PDO) | ✅ |
| **XSS** | `htmlspecialchars()`, CSP headers | ✅ |
| **CSRF** | `View::csrf()` in forms | ✅ |
| **Broken Auth** | Session regeneration, secure cookies | ✅ |
| **Sensitive Data** | HTTPS, password hashing | ✅ |
| **Security Misconfiguration** | `.env` not committed, debug=false | ✅ |

### Session Security
```php
// In config/app.php or bootstrap
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);  // HTTPS only
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);
session_regenerate_id(true);  // After login
```

### Password Policy
```php
// Strong password validation
function validatePassword(string $password): bool {
    return strlen($password) >= 12
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password)
        && preg_match('/[^A-Za-z0-9]/', $password);
}

// Always hash with bcrypt
password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
```

### Rate Limiting (Basic)
```php
// In login controller
$attempts = $_SESSION['login_attempts'] ?? 0;
if ($attempts >= 5) {
    $this->jsonError('Too many attempts. Try again in 15 minutes.', 429);
    return;
}
$_SESSION['login_attempts'] = $attempts + 1;
```

### Security Headers (PHP)
```php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
```

### Log Security Events
```php
function logSecurityEvent(string $event, array $context = []): void {
    $data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_id' => Auth::id() ?? 'guest',
        'context' => $context
    ];
    file_put_contents('storage/logs/security.log', json_encode($data) . "\n", FILE_APPEND);
}

// Usage
logSecurityEvent('login_failed', ['email' => $email]);
logSecurityEvent('permission_denied', ['route' => '/admin']);
```

---

## ⚡ Performance Tuning

### PHP Opcache
```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0        ; Disable in prod (restart PHP after deploy)
opcache.interned_strings_buffer=16
opcache.fast_shutdown=1
```

### MySQL Optimization
```sql
-- Check slow queries
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;

-- Key buffer for MyISAM (if used)
SET GLOBAL key_buffer_size = 256M;

-- InnoDB buffer pool (70-80% of RAM for dedicated DB server)
SET GLOBAL innodb_buffer_pool_size = 2G;
```

### Query Optimization
```php
// ❌ N+1 Problem
foreach (User::all() as $user) {
    echo $user->department->name;  // Query per user!
}

// ✅ Eager loading (single query with JOIN)
$users = Database::query("
    SELECT u.*, d.name as dept_name
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
");
```

### Indexing Strategy
```sql
-- Add indexes for frequently filtered columns
ALTER TABLE budget_requests ADD INDEX idx_fiscal_year (fiscal_year_id);
ALTER TABLE budget_requests ADD INDEX idx_status (status);
ALTER TABLE budget_requests ADD INDEX idx_org (organization_id);

-- Composite index for common filters
ALTER TABLE budget_executions ADD INDEX idx_org_year (organization_id, fiscal_year_id);
```

### Caching Strategies
```php
// Simple file cache
function cache(string $key, callable $callback, int $ttl = 3600) {
    $file = "storage/cache/{$key}.json";
    
    if (file_exists($file) && (time() - filemtime($file)) < $ttl) {
        return json_decode(file_get_contents($file), true);
    }
    
    $data = $callback();
    file_put_contents($file, json_encode($data));
    return $data;
}

// Usage
$stats = cache('dashboard_stats', fn() => Dashboard::getStats(), 300);
```

### Composer Optimization
```bash
composer install --optimize-autoloader --no-dev --classmap-authoritative
```

### Asset Optimization
```bash
# Minify CSS/JS (if using Vite)
npm run build

# Gzip compression (Nginx)
gzip on;
gzip_types text/plain text/css application/json application/javascript;
gzip_min_length 1000;
```

---

## 🚀 Deployment Workflow

### Manual Deployment

```bash
# 1. Pull latest changes
cd /var/www/hr_budget
git pull origin main

# 2. Install Dependencies (Optimized)
composer install --optimize-autoloader --no-dev
npm install --production && npm run build

# 3. Migrate Database (Backup first!)
# php database/run_migrations.php

# 4. Clear Cache
rm -rf storage/cache/*.json
php -r "opcache_reset();" 2>/dev/null || systemctl restart php8.3-fpm

# 5. Verify
curl -s http://localhost/hr_budget/public/login | grep -q "Login" && echo "✅ OK"
```

### Post-Deployment Checks
- [ ] Check `storage/logs/` for errors
- [ ] Test `/login`, `/budgets`, `/requests`
- [ ] Verify file upload works
- [ ] Monitor CPU/Memory for 5 minutes
