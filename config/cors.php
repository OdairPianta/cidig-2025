<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*', '*'], // Added * to allow all paths
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Allow all origins during development
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['*'],
    'max_age' => 0,
    'supports_credentials' => true,
];