---
name: security_hardening
description: Guide for advanced security hardening practices in the HR Budget project.
---

# Security Hardening Guide

Advanced security practices beyond basic authentication.

## 📑 Table of Contents

- [HTTP Security Headers](#-http-security-headers)
- [Input Validation](#-input-validation)
- [CSRF Protection](#-csrf-protection)
- [SQL Injection Prevention](#-sql-injection-prevention)
- [XSS Prevention](#-xss-prevention)
- [Security Scanning](#-security-scanning)

## 🔒 HTTP Security Headers

### Security Headers Middleware

```php
class SecurityHeadersMiddleware
{
    public function handle(): void
    {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // XSS Protection (legacy browsers)
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions Policy
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
        
        // Content Security Policy
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ]);
        header("Content-Security-Policy: {$csp}");
        
        // HSTS (HTTPS only)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
```

### CSP Report-Only Mode

```php
// For testing CSP without breaking the site
$csp = "default-src 'self'; report-uri /api/csp-report";
header("Content-Security-Policy-Report-Only: {$csp}");

// Endpoint to collect violations
public function cspReport(): void
{
    $report = json_decode(file_get_contents('php://input'), true);
    Logger::warning('CSP Violation', $report['csp-report'] ?? []);
}
```

## ✅ Input Validation

### Validation Class

```php
class Validator
{
    private array $data;
    private array $errors = [];
    
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    public function required(string $field, string $message = null): self
    {
        if (empty($this->data[$field])) {
            $this->errors[$field] = $message ?? "กรุณากรอก {$field}";
        }
        return $this;
    }
    
    public function email(string $field): self
    {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'รูปแบบอีเมลไม่ถูกต้อง';
        }
        return $this;
    }
    
    public function numeric(string $field): self
    {
        if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = 'ต้องเป็นตัวเลขเท่านั้น';
        }
        return $this;
    }
    
    public function min(string $field, int $min): self
    {
        if (!empty($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = "ต้องมีอย่างน้อย {$min} ตัวอักษร";
        }
        return $this;
    }
    
    public function max(string $field, int $max): self
    {
        if (!empty($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = "ต้องไม่เกิน {$max} ตัวอักษร";
        }
        return $this;
    }
    
    public function in(string $field, array $allowed): self
    {
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $allowed)) {
            $this->errors[$field] = 'ค่าที่เลือกไม่ถูกต้อง';
        }
        return $this;
    }
    
    public function fails(): bool
    {
        return !empty($this->errors);
    }
    
    public function errors(): array
    {
        return $this->errors;
    }
    
    public function validated(): array
    {
        if ($this->fails()) {
            throw new ValidationException($this->errors);
        }
        return array_intersect_key($this->data, array_flip(array_keys($this->data)));
    }
}

// Usage
$validator = new Validator($_POST);
$validator
    ->required('title')
    ->max('title', 255)
    ->required('amount')
    ->numeric('amount')
    ->required('status')
    ->in('status', ['draft', 'pending', 'approved']);

if ($validator->fails()) {
    return $this->json(['errors' => $validator->errors()], 422);
}
```

### Sanitization

```php
class Sanitizer
{
    public static function string(?string $value): string
    {
        return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
    
    public static function int($value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }
    
    public static function float($value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
    
    public static function email(?string $value): string
    {
        return filter_var(trim($value ?? ''), FILTER_SANITIZE_EMAIL);
    }
    
    public static function filename(?string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $value ?? '');
    }
}
```

## 🛡️ CSRF Protection

### CSRF Token Management

```php
class CSRF
{
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_TTL = 3600; // 1 hour
    
    public static function generate(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::TOKEN_NAME] = [
            'token' => $token,
            'expires' => time() + self::TOKEN_TTL
        ];
        return $token;
    }
    
    public static function token(): string
    {
        if (!isset($_SESSION[self::TOKEN_NAME]) || 
            $_SESSION[self::TOKEN_NAME]['expires'] < time()) {
            return self::generate();
        }
        return $_SESSION[self::TOKEN_NAME]['token'];
    }
    
    public static function verify(?string $token): bool
    {
        if (!$token || !isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }
        
        $stored = $_SESSION[self::TOKEN_NAME];
        
        if ($stored['expires'] < time()) {
            return false;
        }
        
        return hash_equals($stored['token'], $token);
    }
    
    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . self::token() . '">';
    }
    
    public static function meta(): string
    {
        return '<meta name="csrf-token" content="' . self::token() . '">';
    }
}
```

### CSRF Middleware

```php
class CsrfMiddleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];
    
    public function handle(): bool
    {
        if (in_array($_SERVER['REQUEST_METHOD'], self::SAFE_METHODS)) {
            return true;
        }
        
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!CSRF::verify($token)) {
            http_response_code(419);
            echo json_encode(['error' => 'CSRF token mismatch']);
            return false;
        }
        
        return true;
    }
}
```

## 🗄️ SQL Injection Prevention

### Prepared Statements (Required)

```php
// ❌ NEVER do this
$sql = "SELECT * FROM users WHERE id = " . $_GET['id'];

// ✅ Always use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_GET['id']]);

// ✅ Named parameters
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND status = :status");
$stmt->execute(['email' => $email, 'status' => 'active']);
```

### Query Builder with Safe Bindings

```php
class QueryBuilder
{
    private string $table;
    private array $wheres = [];
    private array $bindings = [];
    
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }
    
    public function where(string $column, string $operator, $value): self
    {
        // Whitelist operators
        $allowedOperators = ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'IN'];
        if (!in_array(strtoupper($operator), $allowedOperators)) {
            throw new \InvalidArgumentException('Invalid operator');
        }
        
        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }
    
    public function get(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        
        $stmt = Database::prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll();
    }
}
```

## 🚫 XSS Prevention

### Output Escaping

```php
class Escape
{
    public static function html(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    public static function attr(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    public static function js($value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
    
    public static function url(?string $value): string
    {
        return filter_var($value ?? '', FILTER_SANITIZE_URL);
    }
}

// Short helper
function e(?string $value): string
{
    return Escape::html($value);
}
```

### View Usage

```php
<!-- Always escape user data -->
<h1><?= e($user['name']) ?></h1>
<input type="text" value="<?= e($budget['title']) ?>">

<!-- Safe JSON for JavaScript -->
<script>
const data = <?= Escape::js($data) ?>;
</script>

<!-- Safe URLs -->
<a href="<?= Escape::url($link) ?>">Click here</a>
```

## 🔍 Security Scanning

### OWASP ZAP Integration

```bash
# Run ZAP scan
docker run -t owasp/zap2docker-stable zap-baseline.py \
    -t http://localhost:8000 \
    -r report.html
```

### PHP Security Checks

```bash
# Install security checker
composer require --dev enlightn/security-checker

# Run check
./vendor/bin/security-checker security:check composer.lock
```

### Security Audit Checklist

```markdown
## Weekly Security Checklist

- [ ] Check for dependency vulnerabilities (`composer audit`)
- [ ] Review error logs for attack patterns
- [ ] Verify backup integrity
- [ ] Check for unusual login attempts
- [ ] Review new user registrations
- [ ] Monitor disk space and permissions
```

## 🔐 Additional Hardening

### Rate Limiting per User

```php
class UserRateLimiter
{
    public static function check(int $userId, string $action, int $maxAttempts = 10, int $decayMinutes = 60): bool
    {
        $key = "rate_limit:{$userId}:{$action}";
        $attempts = $_SESSION[$key] ?? ['count' => 0, 'reset_at' => time() + ($decayMinutes * 60)];
        
        if (time() > $attempts['reset_at']) {
            $attempts = ['count' => 0, 'reset_at' => time() + ($decayMinutes * 60)];
        }
        
        if ($attempts['count'] >= $maxAttempts) {
            return false; // Rate limited
        }
        
        $attempts['count']++;
        $_SESSION[$key] = $attempts;
        
        return true;
    }
}

// Usage
if (!UserRateLimiter::check(Auth::id(), 'api_requests', 100, 60)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests']);
    exit;
}
```

### Secure File Uploads

```php
class SecureUpload
{
    private const ALLOWED_MIMES = [
        'image/jpeg', 'image/png', 'image/gif',
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    private const MAX_SIZE = 10 * 1024 * 1024; // 10MB
    
    public static function validate(array $file): array
    {
        $errors = [];
        
        // Check upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'อัพโหลดไฟล์ล้มเหลว';
            return $errors;
        }
        
        // Check size
        if ($file['size'] > self::MAX_SIZE) {
            $errors[] = 'ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 10MB)';
        }
        
        // Check MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        
        if (!in_array($mime, self::ALLOWED_MIMES)) {
            $errors[] = 'ประเภทไฟล์ไม่รองรับ';
        }
        
        // Check for PHP in file content
        $content = file_get_contents($file['tmp_name']);
        if (preg_match('/<\?php|<\?=/i', $content)) {
            $errors[] = 'ไฟล์มีเนื้อหาไม่ปลอดภัย';
        }
        
        return $errors;
    }
    
    public static function store(array $file, string $directory): string
    {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
        $path = rtrim($directory, '/') . '/' . $filename;
        
        move_uploaded_file($file['tmp_name'], $path);
        
        return $filename;
    }
}
```
