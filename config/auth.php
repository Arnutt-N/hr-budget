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
    
    // ThaID (Thai Digital ID / DOPA) OAuth2 — env-driven, DORMANT BY DEFAULT.
    // The feature is "enabled" only when real credentials are present (derived
    // in App\Services\ThaIdConfig), or when THAID_MOCK=true in a non-prod env.
    // There is deliberately NO hardcoded `enabled`/`mock=true` literal here —
    // both must be opt-in via env so the integration never half-works in prod.
    'thaid' => [
        'mock'          => filter_var($_ENV['THAID_MOCK'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'client_id'     => $_ENV['THAID_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['THAID_CLIENT_SECRET'] ?? '',
        'redirect_uri'  => $_ENV['THAID_REDIRECT_URI'] ?? '',
        'authorize_url' => $_ENV['THAID_AUTHORIZE_URL'] ?? 'https://imauth.bora.dopa.go.th/api/v2/oauth2/auth/',
        'token_url'     => $_ENV['THAID_TOKEN_URL']     ?? 'https://imauth.bora.dopa.go.th/api/v2/oauth2/token/',
        'userinfo_url'  => $_ENV['THAID_USERINFO_URL']  ?? 'https://imauth.bora.dopa.go.th/api/v2/oauth2/user/',
        'scope'         => $_ENV['THAID_SCOPE'] ?? 'pid name',
        'pkce'          => filter_var($_ENV['THAID_PKCE'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'client_auth'   => $_ENV['THAID_CLIENT_AUTH'] ?? 'basic', // basic | post
        // OIDC id_token verification (defense-in-depth) — OFF unless jwks_url is
        // set. When configured, a returned id_token is signature-verified against
        // the JWKS and its sub is cross-checked with userinfo. audience defaults
        // to client_id when left blank.
        'jwks_url'      => $_ENV['THAID_JWKS_URL'] ?? '',
        'issuer'        => $_ENV['THAID_ISSUER'] ?? '',
        'audience'      => $_ENV['THAID_AUDIENCE'] ?? '',
        // DOPA userinfo claim names are the one external unknown — overridable
        // without code changes when onboarding confirms the real field names.
        'field_map'     => [
            'sub'            => $_ENV['THAID_FIELD_SUB'] ?? 'sub',
            'name'           => $_ENV['THAID_FIELD_NAME'] ?? 'name',
            'email'          => $_ENV['THAID_FIELD_EMAIL'] ?? 'email',
            'email_verified' => $_ENV['THAID_FIELD_EMAIL_VERIFIED'] ?? 'email_verified',
        ],
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
