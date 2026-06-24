<?php

namespace App\Http\Controllers;

use App\Services\PseoService;
use Illuminate\View\View;

class ProgrammaticSeoController extends Controller
{
    protected PseoService $pseo;

    public function __construct(PseoService $pseo)
    {
        $this->pseo = $pseo;
    }

    public function page(string $pattern, string $slug = ''): View
    {
        $data = $this->pseo->generatePageData($pattern, $slug);

        $data['topCities'] = array_slice($this->pseo->indonesianCities(), 0, 10);
        $data['features'] = array_slice($this->pseo->posFeatures(), 0, 8);
        $data['industries'] = array_slice($this->pseo->industries(), 0, 8);
        $data['relatedPages'] = $this->getRelatedPages($pattern, $slug);
        $data['content'] = $this->buildContent($data);
        $data['heading'] = $this->buildHeading($data);

        return view('pseo.generic', $data);
    }

    public function staticPage(string $slug): View
    {
        $staticTitles = [
            'beli-aplikasi-pos' => 'Beli Aplikasi POS — Source Code Point of Sale Siap Pakai',
            'beli-source-code-pos' => 'Beli Source Code POS Retail — Full Source Code Laravel',
            'jual-source-code-pos' => 'Jual Source Code Aplikasi POS — Siap Pakai & Bergaransi',
            'harga-source-code-pos' => 'Harga Source Code POS Retail — Mulai Rp 4.999.000',
            'source-code-aplikasi-pos' => 'Source Code Aplikasi POS — Point of Sale Terlengkap',
        ];

        $descriptions = [
            'beli-aplikasi-pos' => 'Beli aplikasi POS / Point of Sale source code lengkap. Laravel + Filament, multi-outlet, inventori, laporan keuangan. WA 081296052010.',
            'beli-source-code-pos' => 'Beli source code POS Retail — aplikasi kasir modern siap pakai. 30+ fitur, 52 tabel database, API v1, PSEO built-in. WA 081296052010.',
            'jual-source-code-pos' => 'Jual source code aplikasi POS Retail — sistem kasir lengkap untuk toko, minimarket, restoran. Siap pakai, lifetime update. WA 081296052010.',
            'harga-source-code-pos' => 'Harga source code POS Retail — Rp 4.999.000 (lifetime). Dapat full source code + 6 bulan support + dokumentasi lengkap. WA 081296052010.',
            'source-code-aplikasi-pos' => 'Source code aplikasi POS / Point of Sale — Laravel + FilamentPHP + TailwindCSS. Multi-outlet, payment gateway, laporan. WA 081296052010.',
        ];

        $data = [
            'seoMeta' => [
                'title' => $staticTitles[$slug] ?? ucwords(str_replace('-', ' ', $slug)),
                'description' => $descriptions[$slug] ?? 'Aplikasi POS Retail — solusi point of sale untuk bisnis Indonesia.',
                'canonical' => url("/{$slug}"),
            ],
            'brand' => 'POS Retail',
            'waNumber' => '6281296052010',
            'sourceCodePrice' => 'Rp 4.999.000',
            'pattern' => $slug,
            'slug' => '',
            'city' => null,
            'feature' => null,
            'industry' => null,
            'topCities' => array_slice($this->pseo->indonesianCities(), 0, 10),
            'features' => array_slice($this->pseo->posFeatures(), 0, 8),
            'industries' => array_slice($this->pseo->industries(), 0, 8),
            'relatedPages' => $this->getRelatedPages($slug, ''),
            'content' => "POS Retail adalah aplikasi Point of Sale (POS) / sistem kasir modern untuk bisnis retail Indonesia. Source code siap pakai. WA 081296052010.",
            'heading' => $staticTitles[$slug] ?? 'POS Retail — Source Code Point of Sale',
        ];

        return view('pseo.landing', $data);
    }

    protected function buildHeading(array $data): string
    {
        if ($data['city']) return "Aplikasi POS di {$data['city']['name']} — Point of Sale Terbaik";
        if ($data['industry']) return "Solusi POS untuk Bisnis {$data['industry']['name']}";
        if ($data['feature']) return "Aplikasi POS dengan {$data['feature']['name']}";
        return $data['brand'] . " — Source Code Aplikasi POS";
    }

    protected function buildContent(array $data): string
    {
        $brand = $data['brand'];
        $out = "<p><strong>{$brand}</strong> adalah aplikasi Point of Sale (POS) / sistem kasir modern yang dirancang khusus untuk bisnis retail di Indonesia. ";

        if ($data['city']) {
            $out .= "Jika Anda mencari aplikasi POS di <strong>{$data['city']['name']}</strong> dan sekitarnya, {$brand} adalah solusi tepat. ";
        }

        $out .= "Aplikasi ini dilengkapi fitur lengkap: multi-outlet, inventori real-time, payment gateway dinamis (QRIS, GoPay, OVO), laporan penjualan & keuangan, program loyalitas pelanggan, API v1, role-based access, dan anti-fraud.</p>";

        if ($data['city']) {
            $out .= "<p><strong>Butuh aplikasi POS di {$data['city']['name']}?</strong> {$brand} sudah digunakan oleh berbagai toko retail di Indonesia. Source code bisa dibeli dan di-custom sesuai kebutuhan. <strong>Lifetime update, 6 bulan support.</strong></p>";
        }

        return $out;
    }

    protected function getRelatedPages(string $pattern, string $slug): array
    {
        $cities = array_slice($this->pseo->indonesianCities(), 0, 6);
        $related = [];

        $city = collect($cities)->first(fn($c) => \Illuminate\Support\Str::contains($slug, \Illuminate\Support\Str::slug($c['name'])));
        if ($city) {
            $citySlug = \Illuminate\Support\Str::slug($city['name']);
            $related[] = ['url' => "/aplikasi-pos-{$citySlug}", 'title' => "Aplikasi POS {$city['name']}"];
            $related[] = ['url' => "/source-code-pos-{$citySlug}", 'title' => "Source Code POS {$city['name']}"];
        }

        foreach (array_slice($cities, 0, 5) as $c) {
            $cSlug = \Illuminate\Support\Str::slug($c['name']);
            $related[] = ['url' => "/aplikasi-pos-{$cSlug}", 'title' => "Aplikasi POS {$c['name']}"];
        }

        return $related;
    }
}
