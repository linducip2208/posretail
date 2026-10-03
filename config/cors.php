<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel CORS — aktif untuk API POS (Flutter / SPA)
    |--------------------------------------------------------------------------
    | Isi CORS_ALLOWED_ORIGINS di .env, pisahkan koma. Default '*' untuk
    | kemudahan dev; di produksi isi domain toko, misal:
    | CORS_ALLOWED_ORIGINS=https://posretail.test,https://kasir.toko.id
    */
    'paths' => ['api/*', 'menu/*', 'pos/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '*')))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'Accept', 'X-Socket-Id'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,
];
