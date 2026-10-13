<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

/**
 * Satu sumber kebenaran untuk batas angka yang aman bagi kolom DB.
 *
 * Latar belakang: input angka tanpa batas (mis. stok "1.0E+22") lolos
 * validasi form lalu meledak di MySQL (SQLSTATE 22003/1264) menjadi
 * halaman 500. Helper ini dipakai di tiga lapis:
 *  1. Aturan validasi form Filament (via ::stockRules() / ::moneyRules()),
 *  2. Guard event `saving` pada model (jalur import & API ikut aman),
 *  3. Pesan error Bahasa Indonesia yang ramah untuk user.
 */
class SafeNumber
{
    /**
     * Stok maksimum: 9 digit. Muat di kolom INT maupun BIGINT
     * dan masuk akal untuk bisnis ritel.
     */
    public const STOCK_MAX = 999999999;

    /**
     * Harga maksimum: batas kolom decimal(15,2).
     */
    public const MONEY_MAX = 9999999999999.99;

    /**
     * Garansi maksimum dalam bulan (100 tahun).
     */
    public const WARRANTY_MAX = 1200;

    /**
     * Aturan validasi Laravel untuk field stok (dipakai form & importer).
     */
    public static function stockRules(): array
    {
        return ['integer', 'min:0', 'max:'.static::STOCK_MAX];
    }

    /**
     * Aturan validasi Laravel untuk field uang (dipakai form & importer).
     */
    public static function moneyRules(): array
    {
        return ['numeric', 'min:0', 'max:'.static::MONEY_MAX];
    }

    /**
     * Guard atribut mentah produk. Dipanggil paling awal di event `saving`
     * (sebelum query apa pun) agar pelanggaran menjadi ValidationException
     * — tampil sebagai error field di Filament, 422 di API, baris gagal
     * di importer — bukan halaman 500.
     */
    public static function guardProductAttributes(array $attributes): void
    {
        $errors = [];

        foreach ([
            'min_stock' => 'Stok minimum',
            'max_stock' => 'Stok maksimum',
            'current_stock' => 'Stok saat ini',
        ] as $field => $label) {
            if ($message = static::checkStock($attributes[$field] ?? null, $label)) {
                $errors[$field] = $message;
            }
        }

        foreach ([
            'cost_price' => 'Harga beli',
            'selling_price' => 'Harga jual',
            'wholesale_price' => 'Harga grosir',
            'member_price' => 'Harga member',
        ] as $field => $label) {
            if ($message = static::checkMoney($attributes[$field] ?? null, $label)) {
                $errors[$field] = $message;
            }
        }

        if (array_key_exists('warranty_months', $attributes)) {
            if ($message = static::checkIntRange($attributes['warranty_months'], 'Masa garansi', 0, static::WARRANTY_MAX, 'bulan')) {
                $errors['warranty_months'] = $message;
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Guard atribut mentah varian produk.
     */
    public static function guardVariantAttributes(array $attributes): void
    {
        $errors = [];

        if ($message = static::checkStock($attributes['current_stock'] ?? null, 'Stok varian')) {
            $errors['current_stock'] = $message;
        }

        foreach ([
            'cost_price' => 'Harga beli varian',
            'selling_price' => 'Harga jual varian',
        ] as $field => $label) {
            if ($message = static::checkMoney($attributes[$field] ?? null, $label)) {
                $errors[$field] = $message;
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Kembalikan pesan error bila nilai stok tidak valid, null bila OK.
     * Menolak: non-angka, negatif, pecahan, dan angka di atas STOCK_MAX
     * (termasuk notasi ilmiah seperti 1.0E+22 hasil konversi float).
     */
    public static function checkStock(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return "{$label} harus berupa angka.";
        }

        $number = (float) $value;

        if (! is_finite($number)) {
            return "{$label} tidak valid.";
        }

        if ($number < 0) {
            return "{$label} tidak boleh negatif.";
        }

        if (floor($number) != $number) {
            return "{$label} harus bilangan bulat.";
        }

        if ($number > static::STOCK_MAX) {
            return "{$label} terlalu besar (maksimal ".number_format(static::STOCK_MAX, 0, ',', '.').').';
        }

        return null;
    }

    /**
     * Kembalikan pesan error bila nilai uang tidak valid, null bila OK.
     */
    public static function checkMoney(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return "{$label} harus berupa angka.";
        }

        $number = (float) $value;

        if (! is_finite($number)) {
            return "{$label} tidak valid.";
        }

        if ($number < 0) {
            return "{$label} tidak boleh negatif.";
        }

        if ($number > static::MONEY_MAX) {
            return "{$label} terlalu besar (maksimal Rp ".number_format(static::MONEY_MAX, 2, ',', '.').').';
        }

        return null;
    }

    /**
     * Kembalikan pesan error bila nilai integer di luar rentang, null bila OK.
     */
    public static function checkIntRange(mixed $value, string $label, int $min, int $max, string $unit = ''): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value) || ! is_finite((float) $value) || floor((float) $value) != (float) $value) {
            return "{$label} harus bilangan bulat.";
        }

        if ((float) $value < $min || (float) $value > $max) {
            $suffix = $unit !== '' ? " {$unit}" : '';

            return "{$label} harus antara ".number_format($min, 0, ',', '.').' sampai '.number_format($max, 0, ',', '.').$suffix.'.';
        }

        return null;
    }
}
