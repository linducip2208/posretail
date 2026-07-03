<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeController extends Controller
{
    public function show(Request $request, string $code): Response
    {
        $type = $request->query('type', 'C128');
        $width = (int) $request->query('width', 2);
        $height = (int) $request->query('height', 60);

        $generator = new BarcodeGeneratorPNG;

        $barcode = match (strtoupper($type)) {
            'C128' => $generator->getBarcode($code, $generator::TYPE_CODE_128, $width, $height),
            'C39' => $generator->getBarcode($code, $generator::TYPE_CODE_39, $width, $height),
            'EAN13' => $generator->getBarcode($code, $generator::TYPE_EAN_13, $width, $height),
            'EAN8' => $generator->getBarcode($code, $generator::TYPE_EAN_8, $width, $height),
            default => $generator->getBarcode($code, $generator::TYPE_CODE_128, $width, $height),
        };

        return response($barcode, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
