<?php
/**
 * Application Configuration
 * 
 * HR Budget Management System
 */

return [
    'name' => $_ENV['APP_NAME'] ?? 'HR Budget System',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Bangkok',
    
    // Session Configuration
    'session' => [
        'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 120),
        'secure' => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'http_only' => true,
        'same_site' => 'Lax'
    ],
    
    // Vite Configuration
    'vite' => [
        'dev_server' => $_ENV['VITE_DEV_SERVER'] ?? 'http://localhost:5173',
        'manifest_path' => __DIR__ . '/../public/assets/.vite/manifest.json'
    ],
    
    // Fiscal Year Configuration
    'fiscal_year' => [
        'current' => 2569,
        'start_month' => 10,  // October
        'end_month' => 9      // September
    ],
    
    // Pagination
    'pagination' => [
        'per_page' => 15
    ],
    
    // File Upload
    'upload' => [
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['pdf', 'csv', 'xlsx', 'xls', 'doc', 'docx'],
        'path' => __DIR__ . '/../storage/uploads'
    ]
];
