<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | HARDENED: Only allow specific trusted origins.
    | Do NOT use '*' in production — it allows any website to call your API
    | on behalf of a logged-in user (CSRF-style attack via CORS).
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // In production, replace with your actual frontend domain(s)
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Accept',
        'X-Guest-Token',
    ],

    // Headers the browser is allowed to read from the response
    'exposed_headers' => ['X-Guest-Token'],

    // Cache preflight result for 2 hours (reduces OPTIONS spam)
    'max_age' => 7200,

    'supports_credentials' => true,

];
