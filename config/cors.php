<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://127.0.0.1:8080',    // Perbaiki: hapus / setelah 127.0.0.1
        'http://localhost:8080',     // Tambahkan ini juga untuk fleksibilitas
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];