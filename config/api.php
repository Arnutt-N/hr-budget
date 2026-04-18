<?php
/**
 * REST API Configuration
 *
 * Values pulled from environment (.env) with safe defaults.
 * Never commit real JWT_SECRET — .env is gitignored.
 */

return [
    // JWT signing
    'jwt_secret' => $_ENV['JWT_SECRET'] ?? '',
    'jwt_ttl'    => (int) ($_ENV['JWT_TTL'] ?? 3600),
    'jwt_algo'   => 'HS256',

    // CORS — allowed origins for browser fetch from frontend SPA
    'cors_origins' => array_values(array_filter(
        array_map('trim', explode(',', $_ENV['CORS_ORIGINS'] ?? 'http://localhost:5174'))
    )),

    // API version prefix (for routing)
    'version_prefix' => '/api/v1',
];
