<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Demo Mode
    |--------------------------------------------------------------------------
    | Menampilkan kredensial akun demo di halaman publik (/docs, /login,
    | /admin/login, homepage marketing). Default: nonaktif.
    | Di environment local/testing kredensial tetap tampil untuk kemudahan
    | development. Di production JANGAN aktifkan kecuali instance ini memang
    | instance demo publik yang data-datanya boleh diakses siapa pun.
    */
    'demo_mode' => env('POS_DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Allow Demo Seed di Production
    |--------------------------------------------------------------------------
    | DemoDataSeeder memuat akun berkata sandi lemah (password) dan melakukan
    | truncate banyak tabel. Secara default seeder ini DILEWATI di production.
    | Set POS_ALLOW_DEMO_SEED=true hanya untuk environment demo/staging yang
    | memang ingin di-reset dengan data demo.
    */
    'allow_demo_seed' => env('POS_ALLOW_DEMO_SEED', false),
];
