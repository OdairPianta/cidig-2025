<?php

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie', 
        'livewire/*',
        'admin/*',
        'filament/*',
        'storage/*',
        '*', // Para todas as rotas durante desenvolvimento
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost',
        'http://localhost:80',
        'http://localhost:8000',
        'http://127.0.0.1',
        'http://127.0.0.1:80',
        'http://127.0.0.1:8000',
        '*', // Durante desenvolvimento
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Accept',
        'Authorization', 
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'X-Livewire',
        'X-Socket-ID',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];