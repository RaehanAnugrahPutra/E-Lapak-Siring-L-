<?php

use Illuminate\Http\Middleware\HandleCors;
use Fruitcake\Cors\CorsServiceProvider;
use Fruitcake\Cors\CorsOptions;

return [

    // Route yang kena aturan CORS
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Boleh semua method
    'allowed_methods' => ['*'],

    // Asal (origin) yang boleh manggil API
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    // Header yang boleh dikirim dari frontend
    'allowed_headers' => ['*'],

    // Header yang boleh dibaca frontend
    'exposed_headers' => [],

    'max_age' => 0,

    // Kalau tidak kirim cookie / session dari React, biarkan false
    'supports_credentials' => false,
];
