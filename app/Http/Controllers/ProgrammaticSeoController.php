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

    public function compare(string $slug): View
    {
        $brand = 'POS Retail';
        $parts = preg_split('/-vs-/', $slug, 2);
        $a = ucwords(str_replace('-', ' ', $parts[0] ?? 'POS Retail'));
        $b = ucwords(str_replace('-', ' ', $parts[1] ?? 'Aplikasi Lain'));

        $title = "{$a} vs {$b} — Perbandingan Aplikasi POS Terbaik " . date('Y');
        $description = "Bandingkan {$a} vs {$b}: fitur, harga, multi-outlet, payment gateway, laporan keuangan. {$brand} source code siap pakai. WA 081296052010.";

        $rows = [
            ['Source Code', 'Disertakan (full)', 'Tertutup / SaaS'],
            ['Multi-Outlet', 'Ya, unlimited', 'Tergantung paket'],
            ['Biaya', 'Sekali bayar (lifetime)', 'Langganan bulanan'],
            ['Payment Gateway', 'Dinamis (BYOK)', 'Terbatas / lock-in'],
            ['Laporan Keuangan', 'Lengkap + export PDF/Excel', 'Dasar'],
            ['Custom & Whitelabel', 'Bebas dimodifikasi', 'Tidak bisa'],
            ['API Integrasi', 'API v1 + webhook', 'Terbatas'],
        ];
        $tableRows = '';
        foreach ($rows as $r) {
            $tableRows .= "<tr><td class='py-2 pr-4 font-semibold text-gray-800'>{$r[0]}</td><td class='py-2 pr-4 text-emerald-700'>{$r[1]}</td><td class='py-2 text-gray-600'>{$r[2]}</td></tr>";
        }

        $content = "<p>Memilih antara <strong>{$a}</strong> dan <strong>{$b}</strong>? Berikut perbandingan lengkap aplikasi Point of Sale (POS) untuk membantu Anda memutuskan solusi kasir terbaik bagi bisnis retail di Indonesia. {$brand} hadir sebagai aplikasi POS berbasis Laravel + FilamentPHP dengan source code yang bisa Anda miliki sepenuhnya.</p>";
        $content .= "<div class='overflow-x-auto my-6'><table class='w-full text-sm border-collapse'><thead><tr class='border-b-2 border-gray-200'><th class='py-2 pr-4 text-left'>Aspek</th><th class='py-2 pr-4 text-left'>{$a}</th><th class='py-2 text-left'>{$b}</th></tr></thead><tbody>{$tableRows}</tbody></table></div>";
        $content .= "<p>Keunggulan utama <strong>{$a}</strong> adalah kepemilikan source code penuh — Anda bisa custom, rebrand, dan deploy di server sendiri tanpa biaya langganan bulanan yang terus berjalan. Cocok untuk toko retail, minimarket, restoran, hingga distributor yang ingin kontrol penuh atas sistem kasir mereka.</p>";
        $content .= "<p>Sementara <strong>{$b}</strong> mungkin menawarkan kemudahan setup, model SaaS umumnya mengunci data dan biaya operasional jangka panjang lebih tinggi. Untuk bisnis yang berkembang dan ingin skalabilitas tanpa batasan lisensi per-device, memiliki source code POS adalah investasi yang jauh lebih hemat.</p>";
        $content .= "<p><strong>Kesimpulan:</strong> Jika Anda memprioritaskan kepemilikan, fleksibilitas custom, dan total biaya kepemilikan yang rendah, {$brand} adalah pilihan tepat. Hubungi kami via WhatsApp untuk demo lengkap dan penawaran source code.</p>";

        return view('pseo.generic', $this->baseData($title, $description, url("/bandingkan/{$slug}"), "{$a} vs {$b}: Mana Aplikasi POS Terbaik?", $content, 'FAQPage'));
    }

    public function alternatives(string $slug): View
    {
        $brand = 'POS Retail';
        $target = ucwords(str_replace('-', ' ', $slug));

        $title = "Alternatif {$target} — Aplikasi POS Pengganti Terbaik " . date('Y');
        $description = "Cari alternatif {$target}? {$brand} adalah aplikasi POS source code siap pakai: multi-outlet, inventori, payment gateway, laporan keuangan. WA 081296052010.";

        $reasons = array_slice($this->pseo->posFeatures(), 0, 10);
        $list = '';
        foreach ($reasons as $f) {
            $list .= "<li><strong>{$f['name']}</strong> — tersedia penuh di {$brand} tanpa biaya tambahan.</li>";
        }

        $content = "<p>Mencari <strong>alternatif {$target}</strong> yang lebih hemat dan fleksibel? <strong>{$brand}</strong> adalah aplikasi Point of Sale (POS) berbasis Laravel + FilamentPHP yang bisa Anda miliki source code-nya secara penuh — tanpa langganan bulanan dan bebas dimodifikasi sesuai kebutuhan bisnis Anda.</p>";
        $content .= "<p>Banyak pelaku usaha retail beralih dari {$target} karena mencari solusi yang lebih terjangkau dalam jangka panjang, bisa di-custom, dan mendukung multi-outlet tanpa batasan. {$brand} dirancang khusus untuk pasar Indonesia: harga dalam Rupiah, integrasi QRIS, dan dukungan lokal.</p>";
        $content .= "<p><strong>Mengapa {$brand} jadi alternatif {$target} terbaik:</strong></p><ul class='list-disc pl-5 space-y-1 my-4'>{$list}</ul>";
        $content .= "<p>Dengan kepemilikan source code, Anda terhindar dari vendor lock-in dan biaya langganan yang terus naik. Cocok untuk toko, minimarket, supermarket, restoran, hingga distributor. Hubungi kami via WhatsApp untuk demo dan migrasi data dari {$target}.</p>";

        return view('pseo.generic', $this->baseData($title, $description, url("/alternatif-{$slug}"), "Alternatif {$target}: Beralih ke {$brand}", $content, 'SoftwareApplication'));
    }

    public function bestCategory(string $slug): View
    {
        $brand = 'POS Retail';
        $industry = $this->pseo->industries();
        $match = collect($industry)->first(fn($i) => \Illuminate\Support\Str::slug($i['name']) === $slug);
        $cat = $match['name'] ?? ucwords(str_replace('-', ' ', $slug));

        $title = "Aplikasi POS Terbaik untuk {$cat} " . date('Y') . " — Rekomendasi Top";
        $description = "Daftar aplikasi POS terbaik untuk {$cat} tahun " . date('Y') . ". {$brand} solusi kasir source code: multi-outlet, inventori, laporan. WA 081296052010.";

        $picks = [
            "{$brand} — aplikasi POS source code terlengkap dengan multi-outlet, inventori real-time, dan laporan keuangan. Cocok untuk {$cat} skala kecil hingga besar.",
            "Solusi POS berbasis cloud — praktis namun berbiaya langganan, kurang fleksibel untuk custom kebutuhan {$cat}.",
            "Aplikasi kasir sederhana — cukup untuk transaksi dasar, namun terbatas dalam manajemen stok dan multi-cabang.",
        ];
        $items = '';
        $no = 1;
        foreach ($picks as $p) {
            $items .= "<div class='flex gap-3 mb-4'><div class='flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm'>{$no}</div><p class='text-gray-700'>{$p}</p></div>";
            $no++;
        }

        $content = "<p>Mencari <strong>aplikasi POS terbaik untuk {$cat}</strong>? Memilih sistem kasir yang tepat sangat penting untuk kelancaran operasional bisnis {$cat} Anda. Berikut rekomendasi aplikasi Point of Sale (POS) terbaik tahun " . date('Y') . " yang telah kami kurasi berdasarkan fitur, harga, dan kemudahan penggunaan.</p>";
        $content .= "<div class='my-6'>{$items}</div>";
        $content .= "<p>Untuk bisnis <strong>{$cat}</strong>, kebutuhan utama biasanya mencakup manajemen stok yang akurat, transaksi cepat, laporan penjualan harian, serta dukungan multi-outlet. <strong>{$brand}</strong> memenuhi semua kebutuhan tersebut dengan keunggulan kepemilikan source code penuh — bebas custom dan tanpa biaya langganan bulanan.</p>";
        $content .= "<p>Fitur yang sangat berguna untuk {$cat} antara lain: barcode scanner, stok opname, transfer stok antar cabang, program loyalitas pelanggan, integrasi payment gateway (QRIS, GoPay, OVO), dan laporan profit margin per produk. Semua tersedia dalam satu dashboard terintegrasi.</p>";
        $content .= "<p><strong>Kesimpulan:</strong> {$brand} adalah pilihan terbaik untuk {$cat} yang menginginkan solusi POS lengkap, hemat, dan dapat dimiliki selamanya. Hubungi kami via WhatsApp untuk demo dan penawaran source code.</p>";

        return view('pseo.generic', $this->baseData($title, $description, url("/best-{$slug}"), "Aplikasi POS Terbaik untuk {$cat}", $content, 'ItemList'));
    }

    protected function baseData(string $title, string $description, string $canonical, string $heading, string $content, string $schemaType = 'SoftwareApplication'): array
    {
        return [
            'seoMeta' => [
                'title' => $title,
                'description' => $description,
                'canonical' => $canonical,
                'schemaType' => $schemaType,
            ],
            'brand' => 'POS Retail',
            'waNumber' => '6281296052010',
            'sourceCodePrice' => 'Rp 4.999.000',
            'pattern' => '',
            'slug' => '',
            'city' => null,
            'feature' => null,
            'industry' => null,
            'topCities' => array_slice($this->pseo->indonesianCities(), 0, 10),
            'features' => array_slice($this->pseo->posFeatures(), 0, 8),
            'industries' => array_slice($this->pseo->industries(), 0, 8),
            'relatedPages' => [],
            'content' => $content,
            'heading' => $heading,
        ];
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
