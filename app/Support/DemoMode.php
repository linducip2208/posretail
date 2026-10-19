<?php

namespace App\Support;

/**
 * Sumber tunggal keputusan "mode demo" untuk seluruh aplikasi.
 *
 * Kredensial akun demo hanya boleh tampil di halaman publik bila instance
 * ini memang instance demo (POS_DEMO_MODE=true) atau sedang berjalan di
 * environment development (local/testing). Di production default: sembunyi.
 */
class DemoMode
{
    public static function enabled(): bool
    {
        return (bool) config('pos.demo_mode') || app()->environment(['local', 'testing']);
    }
}
