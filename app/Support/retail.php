<?php

// Helper global khusus tema POS Retail (dimuat via composer autoload files).
// Guard function_exists agar aman di semua konteks (Octane, testing, dsb).

if (! function_exists('tillora_rp')) {
    function tillora_rp($n): string
    {
        return 'Rp '.number_format((float) $n, 0, ',', '.');
    }
}

if (! function_exists('tillora_pimg')) {
    function tillora_pimg($img): string
    {
        if (! $img) {
            return '/app-assets/images/products/mug.svg';
        }
        if (str_starts_with($img, 'http')) {
            return $img;
        }

        return str_starts_with($img, 'storage/') ? asset($img) : asset('storage/'.$img);
    }
}
