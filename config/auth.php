<?php
/**
 * Authentication Configuration
 * 
 * HR Budget Management System
 */

return [
    // Default authentication method
    'default' => 'email', // email, ldap, thaid
    
    // Email domain restriction
    'allowed_domains' => ['moj.go.th'],
    
    // Password settings
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_number' => true,
        'require_special' => false
    ],
    
    // Session settings
    'session' => [
        'key' => 'hr_budget_user',
        'lifetime' => 120 // minutes
    ],
    
    // ThaID Configuration (Mock)
    'thaid' => [
        'enabled' => true,
        'mock' => true, // Use mock instead of real ThaID
        'client_id' => $_ENV['THAID_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['THAID_CLIENT_SECRET'] ?? '',
        'redirect_uri' => $_ENV['THAID_REDIRECT_URI'] ?? '',
        'authorize_url' => 'https://imauth.bora.dopa.go.th/api/v2/oauth2/auth/',
        'token_url' => 'https://imauth.bora.dopa.go.th/api/v2/oauth2/token/'
    ],
    
    // LDAP Configuration (Future)
    'ldap' => [
        'enabled' => false,
        'host' => $_ENV['LDAP_HOST'] ?? '',
        'port' => (int) ($_ENV['LDAP_PORT'] ?? 389),
        'base_dn' => $_ENV['LDAP_BASE_DN'] ?? '',
        'username' => $_ENV['LDAP_USERNAME'] ?? '',
        'password' => $_ENV['LDAP_PASSWORD'] ?? ''
    ],
    
    // User roles
    'roles' => [
        'super_admin' => [
            'name' => 'Super Admin',
            'permissions' => ['*']
        ],
        'admin' => [
            'name' => 'Admin',
            'permissions' => ['users.manage', 'budgets.*', 'requests.*', 'reports.*', 'files.*']
        ],
        'editor' => [
            'name' => 'Editor',
            'permissions' => ['budgets.create', 'budgets.edit', 'requests.*', 'reports.view', 'files.*']
        ],
        'viewer' => [
            'name' => 'Viewer',
            'permissions' => ['budgets.view', 'requests.view', 'reports.view', 'files.view']
        ]
    ]
];
