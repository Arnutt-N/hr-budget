---
name: two_factor_auth
description: Guide for implementing Two-Factor Authentication (2FA) and SSO in the HR Budget project.
---

# Two-Factor Authentication & SSO Guide

Enhanced security through multi-factor authentication.

## 📑 Table of Contents

- [TOTP Implementation](#-totp-implementation)
- [Backup Codes](#-backup-codes)
- [SSO Integration](#-sso-integration)

## 🔑 TOTP Implementation

### TOTP Helper Class

```php
class TOTP
{
    private const DIGITS = 6;
    private const PERIOD = 30;
    
    public static function generateSecret(): string
    {
        return bin2hex(random_bytes(20));
    }
    
    public static function getCode(string $secret): string
    {
        $time = floor(time() / self::PERIOD);
        $hash = hash_hmac('sha1', pack('J', $time), hex2bin($secret), true);
        $offset = ord($hash[19]) & 0x0f;
        $code = (ord($hash[$offset]) & 0x7f) << 24 |
                (ord($hash[$offset+1]) & 0xff) << 16 |
                (ord($hash[$offset+2]) & 0xff) << 8 |
                (ord($hash[$offset+3]) & 0xff);
        return str_pad($code % pow(10, self::DIGITS), self::DIGITS, '0', STR_PAD_LEFT);
    }
    
    public static function verify(string $secret, string $code): bool
    {
        // Check current and adjacent windows
        for ($i = -1; $i <= 1; $i++) {
            $time = floor(time() / self::PERIOD) + $i;
            if (hash_equals(self::getCodeAtTime($secret, $time), $code)) {
                return true;
            }
        }
        return false;
    }
    
    public static function getQRCodeUrl(string $secret, string $email): string
    {
        $issuer = 'HR%20Budget';
        $otpauth = "otpauth://totp/{$issuer}:{$email}?secret=" . 
                   self::base32Encode(hex2bin($secret)) . 
                   "&issuer={$issuer}&digits=6";
        return "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($otpauth);
    }
}
```

### 2FA Flow

```php
// Enable 2FA
public function enable2FA(): void
{
    $secret = TOTP::generateSecret();
    $_SESSION['2fa_temp_secret'] = $secret;
    
    $this->json([
        'secret' => $secret,
        'qr_url' => TOTP::getQRCodeUrl($secret, Auth::user()['email'])
    ]);
}

// Confirm 2FA
public function confirm2FA(Request $request): void
{
    $code = $request->post('code');
    $secret = $_SESSION['2fa_temp_secret'];
    
    if (!TOTP::verify($secret, $code)) {
        $this->error('รหัสไม่ถูกต้อง');
        return;
    }
    
    User::update(Auth::id(), ['two_factor_secret' => $secret]);
    unset($_SESSION['2fa_temp_secret']);
    
    $this->success('เปิดใช้งาน 2FA สำเร็จ');
}

// Login with 2FA
public function verify2FA(Request $request): void
{
    $code = $request->post('code');
    $userId = $_SESSION['2fa_pending_user'];
    
    $user = User::find($userId);
    
    if (!TOTP::verify($user['two_factor_secret'], $code)) {
        $this->error('รหัสไม่ถูกต้อง');
        return;
    }
    
    $_SESSION['user_id'] = $userId;
    unset($_SESSION['2fa_pending_user']);
    
    Router::redirect('/dashboard');
}
```

## 🔐 Backup Codes

```php
class BackupCodes
{
    public static function generate(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }
    
    public static function verify(int $userId, string $code): bool
    {
        $stored = Database::query(
            "SELECT id FROM backup_codes WHERE user_id = ? AND code = ? AND used_at IS NULL",
            [$userId, strtoupper($code)]
        )->fetch();
        
        if ($stored) {
            Database::exec("UPDATE backup_codes SET used_at = NOW() WHERE id = ?", [$stored['id']]);
            return true;
        }
        return false;
    }
}
```

## 🔗 SSO Integration

### OAuth2 Client (Google Example)

```php
class GoogleAuth
{
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    
    public function getAuthUrl(): string
    {
        return "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'state' => $_SESSION['oauth_state'] = bin2hex(random_bytes(16))
        ]);
    }
    
    public function handleCallback(string $code): array
    {
        // Exchange code for token
        $token = $this->getToken($code);
        
        // Get user info
        $userInfo = $this->getUserInfo($token);
        
        return $userInfo;
    }
}
```

### SSO Login Flow

```php
public function ssoCallback(Request $request): void
{
    $userInfo = $this->googleAuth->handleCallback($request->get('code'));
    
    $user = User::findByEmail($userInfo['email']);
    
    if (!$user) {
        // Auto-register or reject
        $this->error('ไม่พบบัญชีในระบบ');
        return;
    }
    
    $_SESSION['user_id'] = $user['id'];
    Router::redirect('/dashboard');
}
```
