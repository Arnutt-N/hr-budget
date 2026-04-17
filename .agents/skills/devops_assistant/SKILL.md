---
name: devops_assistant
description: Guide for server configuration, deployment, and security hardening for the HR Budget project.
---

# DevOps Assistant

Guide for deploying, securing, and maintaining the HR Budget application.

## 📑 Table of Contents
- [Server Requirements](#-server-requirements)
- [Environment Configuration](#-environment-configuration)
- [Web Server Setup](#-web-server-setup)
- [Security Hardening](#-security-hardening)
- [Deployment Workflow](#-deployment-workflow)
- [Optimization](#-optimization)

## 🖥️ Server Requirements

| Component | Requirement | Recommended |
|:----------|:------------|:------------|
| **OS** | Linux / Windows | Ubuntu 22.04 LTS |
| **PHP** | 8.3+ | 8.3 (Latest Stable) |
| **Database** | MySQL 8.0+ | MySQL 8.0 / MariaDB 10.6+ |
| **Web Server**| Apache / Nginx | Nginx (Performance) / Apache (Ease of use) |

**Required Extensions:**
- `pdo_mysql`, `mbstring`, `openssl`, `json`, `curl`, `gd`, `xml`, `zip`

## ⚙️ Environment Configuration

### Production `.env`
Ensure these variables are set correctly for production:

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

### Nginx Configuration (Recommended_Block)

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
- **Files**: `644` (User: RW, Group: R, Other: R)
- **Directories**: `755` (User: RWX, Group: RX, Other: RX)
- **Sensitive**: `storage/` and `bootstrap/cache/` need write access (`775` or `www-data` ownership).

```bash
chown -R www-data:www-data /var/www/hr_budget
find /var/www/hr_budget -type f -exec chmod 644 {} \;
find /var/www/hr_budget -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
```

### 2. Disable Sensitive Files
Ensure web server blocks access to:
- `.env`
- `.git/`
- `composer.json` / `composer.lock`
- `package.json`

## 🚀 Deployment Workflow

### Manual Deployment (Git Pull)

```bash
# 1. Pull latest changes
cd /var/www/hr_budget
git pull origin main

# 2. Install Dependencies (Optimized)
composer install --optimize-autoloader --no-dev
npm install --production

# 3. Migrate Database (Check Schema first!)
# IMPORTANT: Backup DB before running this
# php python/migrate.php (or custom runner)

# 4. Clear Cache (if any)
# rm -rf bootstrap/cache/*.php
```

### Post-Deployment Checks
- Check `storage/logs/` for errors
- Verify critical paths (`/login`, `/budgets`)
- Test file uploads permissions

## ⚡ Optimization

### PHP Opcache
Enable Opcache in `php.ini` for prod:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0  ; Re-deploy needs cache clear/restart
```

### Composer
Always run with `--no-dev` to skip testing libraries.
```bash
composer dump-autoload --optimize
```
