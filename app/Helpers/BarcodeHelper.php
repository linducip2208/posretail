<?php

namespace App\Helpers;

class BarcodeHelper
{
    public static function generate(): string
    {
        $prefix = '200';
        $number = (string) random_int(10000000000, 99999999999);

        $digits = $prefix . $number;
        $checksum = 0;

        for ($i = 0; $i < 12; $i++) {
            $checksum += ($i % 2 === 0) ? (int) $digits[$i] * 1 : (int) $digits[$i] * 3;
        }

        $checksum = (10 - ($checksum % 10)) % 10;

        return $digits . $checksum;
    }

    public static function isValidEan13(string $barcode): bool
    {
        if (strlen($barcode) !== 13 || ! ctype_digit($barcode)) {
            return false;
        }

        $checksum = 0;
        for ($i = 0; $i < 12; $i++) {
            $checksum += ($i % 2 === 0) ? (int) $barcode[$i] * 1 : (int) $barcode[$i] * 3;
        }

        $checksum = (10 - ($checksum % 10)) % 10;

        return (int) $barcode[12] === $checksum;
    }
}
