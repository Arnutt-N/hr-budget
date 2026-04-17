---
name: i18n_localization
description: Guide for Internationalisation (i18n) and Localization (l10n) in the HR Budget project.
---

# Internationalisation & Localization Guide

Standards for multi-language support in the HR Budget application.

## 📑 Table of Contents

- [Overview](#-overview)
- [Translation Files](#-translation-files)
- [PHP Implementation](#-php-implementation)
- [JavaScript Implementation](#-javascript-implementation)
- [Date & Number Formatting](#-date--number-formatting)
- [Best Practices](#-best-practices)

## 📋 Overview

### Supported Languages

| Language | Code | Status |
|:---------|:----:|:------:|
| ไทย (Thai) | `th` | ✅ Primary |
| English | `en` | ✅ Secondary |

### Directory Structure

```
resources/
├── lang/
│   ├── th/
│   │   ├── common.php
│   │   ├── validation.php
│   │   ├── budgets.php
│   │   └── requests.php
│   └── en/
│       ├── common.php
│       ├── validation.php
│       ├── budgets.php
│       └── requests.php
```

## 📁 Translation Files

### File Format

```php
// resources/lang/th/common.php
return [
    'app_name' => 'ระบบบริหารงบประมาณบุคลากร',
    'dashboard' => 'แดชบอร์ด',
    'logout' => 'ออกจากระบบ',
    'save' => 'บันทึก',
    'cancel' => 'ยกเลิก',
    'delete' => 'ลบ',
    'edit' => 'แก้ไข',
    'search' => 'ค้นหา',
    'actions' => 'การดำเนินการ',
    'confirm_delete' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
];

// resources/lang/en/common.php
return [
    'app_name' => 'HR Budget Management System',
    'dashboard' => 'Dashboard',
    'logout' => 'Logout',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'search' => 'Search',
    'actions' => 'Actions',
    'confirm_delete' => 'Are you sure you want to delete this item?',
];
```

### Nested Translations

```php
// resources/lang/th/budgets.php
return [
    'title' => 'งบประมาณ',
    'create' => 'สร้างงบประมาณใหม่',
    'status' => [
        'draft' => 'ร่าง',
        'pending' => 'รออนุมัติ',
        'approved' => 'อนุมัติแล้ว',
        'rejected' => 'ไม่อนุมัติ',
    ],
    'fields' => [
        'name' => 'ชื่องบประมาณ',
        'amount' => 'จำนวนเงิน',
        'fiscal_year' => 'ปีงบประมาณ',
    ],
];
```

## 🐘 PHP Implementation

### Translation Helper

```php
// src/Helpers/Lang.php
class Lang
{
    private static array $translations = [];
    private static string $locale = 'th';
    
    public static function setLocale(string $locale): void
    {
        self::$locale = $locale;
        $_SESSION['locale'] = $locale;
    }
    
    public static function getLocale(): string
    {
        return $_SESSION['locale'] ?? self::$locale;
    }
    
    public static function get(string $key, array $replace = []): string
    {
        $locale = self::getLocale();
        
        // Parse key (e.g., 'budgets.status.draft')
        $parts = explode('.', $key);
        $file = array_shift($parts);
        
        // Load translation file if not cached
        if (!isset(self::$translations[$locale][$file])) {
            $path = __DIR__ . "/../../resources/lang/{$locale}/{$file}.php";
            self::$translations[$locale][$file] = file_exists($path) 
                ? require $path 
                : [];
        }
        
        // Get nested value
        $value = self::$translations[$locale][$file];
        foreach ($parts as $part) {
            $value = $value[$part] ?? null;
            if ($value === null) return $key; // Fallback to key
        }
        
        // Replace placeholders
        foreach ($replace as $search => $replacement) {
            $value = str_replace(":{$search}", $replacement, $value);
        }
        
        return $value;
    }
}

// Global helper function
function __($key, array $replace = []): string
{
    return Lang::get($key, $replace);
}
```

### Usage in Views

```php
<!-- resources/views/budgets/index.php -->
<h1><?= __('budgets.title') ?></h1>

<a href="/budgets/create" class="btn-primary">
    <?= __('budgets.create') ?>
</a>

<table>
    <tr>
        <th><?= __('budgets.fields.name') ?></th>
        <th><?= __('budgets.fields.amount') ?></th>
        <th><?= __('common.actions') ?></th>
    </tr>
</table>

<!-- With placeholders -->
<p><?= __('common.welcome', ['name' => $user['name']]) ?></p>
<!-- "สวัสดี :name" → "สวัสดี สมชาย" -->
```

### Language Switcher

```php
// Controller
public function switchLanguage(string $locale): void
{
    if (in_array($locale, ['th', 'en'])) {
        Lang::setLocale($locale);
    }
    Router::back();
}

// View
<div class="language-switcher">
    <a href="/lang/th" class="<?= Lang::getLocale() === 'th' ? 'active' : '' ?>">TH</a>
    <a href="/lang/en" class="<?= Lang::getLocale() === 'en' ? 'active' : '' ?>">EN</a>
</div>
```

## 🌐 JavaScript Implementation

### JSON Translation Files

```javascript
// public/js/lang/th.json
{
    "confirm_delete": "คุณแน่ใจหรือไม่ที่จะลบรายการนี้?",
    "loading": "กำลังโหลด...",
    "success": "สำเร็จ",
    "error": "เกิดข้อผิดพลาด",
    "validation": {
        "required": "กรุณากรอกข้อมูล",
        "email": "รูปแบบอีเมลไม่ถูกต้อง"
    }
}
```

### JavaScript Helper

```javascript
// public/js/i18n.js
class I18n {
    constructor(locale = 'th') {
        this.locale = locale;
        this.translations = {};
    }

    async load() {
        const response = await fetch(`/js/lang/${this.locale}.json`);
        this.translations = await response.json();
    }

    t(key, replace = {}) {
        let value = key.split('.').reduce((obj, k) => obj?.[k], this.translations);
        
        if (!value) return key;
        
        Object.entries(replace).forEach(([k, v]) => {
            value = value.replace(`:${k}`, v);
        });
        
        return value;
    }
}

// Usage
const i18n = new I18n(document.documentElement.lang);
await i18n.load();

confirm(i18n.t('confirm_delete'));
```

## 📅 Date & Number Formatting

### Thai Buddhist Era Date

```php
class ThaiDate
{
    public static function format(string $date, string $format = 'd M Y'): string
    {
        $timestamp = strtotime($date);
        
        $thaiMonths = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];
        
        $day = date('j', $timestamp);
        $month = $thaiMonths[(int)date('n', $timestamp)];
        $year = (int)date('Y', $timestamp) + 543; // Buddhist Era
        
        return "{$day} {$month} {$year}";
    }
    
    public static function fiscalYear(int $year = null): int
    {
        $year = $year ?? (int)date('Y');
        return $year + 543;
    }
}

// Usage
echo ThaiDate::format('2025-01-15'); // "15 ม.ค. 2568"
echo ThaiDate::fiscalYear(2025);      // 2568
```

### Number Formatting

```php
class NumberFormat
{
    public static function currency(float $amount, string $locale = 'th'): string
    {
        if ($locale === 'th') {
            return number_format($amount, 2) . ' บาท';
        }
        return '฿' . number_format($amount, 2);
    }
    
    public static function percentage(float $value): string
    {
        return number_format($value, 2) . '%';
    }
}

// Usage
echo NumberFormat::currency(1234567.89, 'th'); // "1,234,567.89 บาท"
```

## ✅ Best Practices

### 1. Always Use Translation Keys

```php
// ❌ Bad - Hardcoded text
<button>บันทึก</button>

// ✅ Good - Translation key
<button><?= __('common.save') ?></button>
```

### 2. Use Meaningful Keys

```php
// ❌ Bad
'msg1' => 'บันทึกสำเร็จ'

// ✅ Good
'messages.save_success' => 'บันทึกสำเร็จ'
```

### 3. Group by Feature

```
lang/th/
├── common.php      # Shared UI elements
├── validation.php  # Form validation messages
├── budgets.php     # Budget module
├── requests.php    # Request module
└── reports.php     # Report module
```

### 4. Handle Pluralization

```php
// Translation file with ICU MessageFormat
'items' => '{count, plural, =0{ไม่มีรายการ} =1{1 รายการ} other{# รายการ}}',
'files' => '{count, plural, =0{no files} =1{one file} other{# files}}',

// Advanced pluralization helper
public static function choice(string $key, int $count, array $replace = []): string
{
    $translation = self::get($key, $replace);
    
    // Simple plural for Thai (same form)
    if (self::getLocale() === 'th') {
        return str_replace('#', $count, $translation);
    }
    
    // English plurals
    if (preg_match('/\{count,\s*plural,(.+)\}/i', $translation, $matches)) {
        $rules = $matches[1];
        
        if ($count === 0 && preg_match('/=0\{([^}]+)\}/', $rules, $m)) {
            return $m[1];
        } elseif ($count === 1 && preg_match('/=1\{([^}]+)\}/', $rules, $m)) {
            return $m[1];
        } elseif (preg_match('/other\{([^}]+)\}/', $rules, $m)) {
            return str_replace('#', $count, $m[1]);
        }
    }
    
    return str_replace(':count', $count, $translation);
}

// Usage
echo Lang::choice('items', 0);  // "ไม่มีรายการ"
echo Lang::choice('items', 1);  // "1 รายการ"
echo Lang::choice('items', 5);  // "5 รายการ"
```

### 5. Fallback Language

```php
public static function get(string $key, array $replace = []): string
{
    $value = self::getFromLocale(self::getLocale(), $key);
    
    // Fallback to Thai if not found
    if ($value === $key && self::getLocale() !== 'th') {
        $value = self::getFromLocale('th', $key);
    }
    
    // Replace placeholders
    foreach ($replace as $search => $replacement) {
        $value = str_replace(":{$search}", $replacement, $value);
    }
    
    return $value;
}
```

## 🌍 Advanced Features

### Locale Detection

```php
class LocaleDetector
{
    public static function detect(): string
    {
        // 1. Check session
        if (isset($_SESSION['locale'])) {
            return $_SESSION['locale'];
        }
        
        // 2. Check cookie
        if (isset($_COOKIE['locale'])) {
            return $_COOKIE['locale'];
        }
        
        // 3. Check Accept-Language header
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $locales = self::parseAcceptLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($locales as $locale) {
                $lang = substr($locale, 0, 2);
                if (in_array($lang, ['th', 'en'])) {
                    return $lang;
                }
            }
        }
        
        // 4. Default to Thai
        return 'th';
    }
    
    private static function parseAcceptLanguage(string $header): array
    {
        $languages = [];
        
        foreach (explode(',', $header) as $lang) {
            $parts = explode(';q=', $lang);
            $locale = trim($parts[0]);
            $quality = isset($parts[1]) ? (float)$parts[1] : 1.0;
            $languages[] = ['locale' => $locale, 'quality' => $quality];
        }
        
        usort($languages, fn($a, $b) => $b['quality'] <=> $a['quality']);
        
        return array_column($languages, 'locale');
    }
}

// Auto-detect on first visit
if (!isset($_SESSION['locale'])) {
    Lang::setLocale(LocaleDetector::detect());
}
```

### RTL Support

```php
class LayoutHelper
{
    private const RTL_LOCALES = ['ar', 'he', 'fa', 'ur'];
    
    public static function isRtl(?string $locale = null): bool
    {
        $locale = $locale ?? Lang::getLocale();
        return in_array($locale, self::RTL_LOCALES);
    }
    
    public static function dir(?string $locale = null): string
    {
        return self::isRtl($locale) ? 'rtl' : 'ltr';
    }
}
```

```html
<!DOCTYPE html>
<html lang="<?= Lang::getLocale() ?>" dir="<?= LayoutHelper::dir() ?>">
<head>
    <link rel="stylesheet" href="<?= LayoutHelper::isRtl() ? 'css/rtl.css' : 'css/ltr.css' ?>">
</head>
```

### PHP Intl Extension Integration

```php
class IntlFormatter
{
    public static function formatDate(string $date, ?string $locale = null): string
    {
        $locale = $locale ?? Lang::getLocale();
        
        // Use IntlDateFormatter for proper locale formatting
        $formatter = new \IntlDateFormatter(
            $locale === 'th' ? 'th_TH@calendar=buddhist' : $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE
        );
        
        return $formatter->format(strtotime($date));
    }
    
    public static function formatCurrency(float $amount, string $currency = 'THB', ?string $locale = null): string
    {
        $locale = $locale ?? Lang::getLocale();
        
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency);
    }
    
    public static function formatNumber(float $number, ?string $locale = null): string
    {
        $locale = $locale ?? Lang::getLocale();
        
        $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        return $formatter->format($number);
    }
}

// Usage
echo IntlFormatter::formatDate('2025-01-15');           // "15 ม.ค. 2568" (Thai)
echo IntlFormatter::formatCurrency(1234567.89, 'THB');  // "฿1,234,567.89"
echo IntlFormatter::formatNumber(1234567.89);           // "1,234,567.89"
```

### Translation Management

```php
class TranslationManager
{
    public static function exportToJson(string $locale): string
    {
        $translations = [];
        $path = __DIR__ . "/../../resources/lang/{$locale}/";
        
        foreach (glob($path . '*.php') as $file) {
            $namespace = basename($file, '.php');
            $translations[$namespace] = require $file;
        }
        
        return json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    public static function findMissing(string $sourceLocale = 'th', string $targetLocale = 'en'): array
    {
        $missing = [];
        $sourcePath = __DIR__ . "/../../resources/lang/{$sourceLocale}/";
        $targetPath = __DIR__ . "/../../resources/lang/{$targetLocale}/";
        
        foreach (glob($sourcePath . '*.php') as $file) {
            $namespace = basename($file, '.php');
            $source = require $file;
            $target = file_exists($targetPath . $namespace . '.php') 
                ? require $targetPath . $namespace . '.php' 
                : [];
            
            $missing[$namespace] = array_diff_key($source, $target);
        }
        
        return array_filter($missing);
    }
}

// Find untranslated keys
$missing = TranslationManager::findMissing('th', 'en');
foreach ($missing as $ns => $keys) {
    echo "Missing in {$ns}: " . count($keys) . " keys\n";
}
```
