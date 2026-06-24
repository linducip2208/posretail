<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PseoService
{
    protected string $brand = 'POS Retail';
    protected string $waNumber;
    protected string $sourceCodePrice;

    public function __construct()
    {
        $this->waNumber = \App\Models\SystemSetting::getValue('whatsapp_number', '6281296052010');
        $this->sourceCodePrice = \App\Models\SystemSetting::getValue('pos_price', 'Rp 4.999.000');
    }

    public function getAllPages(): array
    {
        return Cache::remember('pseo.all_pages', 86400, function () {
            $pages = [];

            $cities = array_slice($this->indonesianCities(), 0, 100);
            $features = $this->posFeatures();
            $industries = $this->industries();

            $pages = array_merge($pages, $this->cityPatterns($cities));
            $pages = array_merge($pages, $this->industryPatterns($industries));
            $pages = array_merge($pages, $this->featurePatterns($features));
            $pages = array_merge($pages, $this->sourceCodePatterns($cities));
            $pages = array_merge($pages, $this->cityFeatureCombos($cities, array_slice($features, 0, 20)));
            $pages = array_merge($pages, $this->cityIndustryCombos($cities, $industries));
            $pages = array_merge($pages, $this->staticPatterns());

            return $pages;
        });
    }

    public function generatePageData(string $pattern, string $slug): array
    {
        $city = $this->findCity($slug);
        $feature = $this->findFeature($slug);
        $industry = $this->findIndustry($slug);

        $data = [
            'seoMeta' => [
                'title' => "Aplikasi POS Retail — Solusi Kasir Modern",
                'description' => "Source code aplikasi POS / Point of Sale siap pakai. Kelola transaksi, stok, laporan, multi-outlet.",
                'canonical' => url('/'),
            ],
            'brand' => $this->brand,
            'waNumber' => $this->waNumber,
            'sourceCodePrice' => $this->sourceCodePrice,
            'pattern' => $pattern,
            'slug' => $slug,
            'city' => $city,
            'feature' => $feature,
            'industry' => $industry,
        ];

        $cityName = $city['name'] ?? '';
        $featureName = $feature['name'] ?? '';
        $industryName = $industry['name'] ?? '';

        $titleMap = [
            'aplikasi-pos' => "Aplikasi POS di {$cityName} — Point of Sale Terbaik",
            'software-kasir' => "Software Kasir di {$cityName} — Solusi POS Retail",
            'sistem-kasir' => "Sistem Kasir {$cityName} — Point of Sale Modern",
            'program-kasir' => "Program Kasir di {$cityName} — POS Retail Source Code",
            'aplikasi-toko' => "Aplikasi Toko di {$cityName} — POS Multi-Outlet",
            'aplikasi-kasir' => "Aplikasi Kasir {$cityName} — POS System Terpercaya",
            'point-of-sale' => "Point of Sale di {$cityName} — Aplikasi Kasir Modern",
            'pos-system' => "POS System {$cityName} — Source Code Point of Sale",
            'source-code-pos' => "Source Code POS {$cityName} — Jual Beli Aplikasi Kasir",
            'beli-aplikasi-pos' => "Beli Aplikasi POS di {$cityName} — Harga Source Code",
            'beli-source-code' => "Beli Source Code Aplikasi POS {$cityName}",
            'harga-source-code' => "Harga Source Code POS di {$cityName} — {$this->sourceCodePrice}",
            'jual-aplikasi-kasir' => "Jual Aplikasi Kasir {$cityName} — Source Code POS Retail",
            'aplikasi-pos-murah' => "Aplikasi POS Murah {$cityName} — Harga Terjangkau",
            'aplikasi-pos-terbaik' => "Aplikasi POS Terbaik di {$cityName} — Rekomendasi",
            'aplikasi-pos-terjangkau' => "Aplikasi POS Terjangkau {$cityName} — Budget Friendly",
            'rekomendasi-aplikasi-pos' => "Rekomendasi Aplikasi POS {$cityName} — Pilihan Terbaik",
            'review-aplikasi-pos' => "Review Aplikasi POS {$cityName} — Testimoni Pengguna",
            'cara-memilih-pos' => "Cara Memilih Aplikasi POS di {$cityName} — Panduan Lengkap",
            'tips-memilih-kasir' => "Tips Memilih Software Kasir {$cityName}",
            'daftar-aplikasi-pos' => "Daftar Aplikasi POS Terbaik di {$cityName}",
            'pos-cloud-vs-lokal' => "POS Cloud vs Lokal di {$cityName} — Mana Lebih Baik?",
            'aplikasi-pos-vs-manual' => "Aplikasi POS vs Manual di {$cityName} — Perbandingan",
            // Industry
            'aplikasi-pos-untuk' => "Aplikasi POS untuk {$industryName} — Solusi Kasir",
            'software-kasir-untuk' => "Software Kasir untuk {$industryName} — POS Retail",
            'pos-untuk' => "POS untuk {$industryName} — Aplikasi Point of Sale",
            // Feature
            'aplikasi-kasir-dengan' => "Aplikasi Kasir dengan {$featureName} — POS Retail",
            'pos-dengan' => "POS dengan {$featureName} — Point of Sale Lengkap",
            'sistem-kasir-dengan' => "Sistem Kasir dengan {$featureName} — POS Modern",
            // City+Feature combo
            'aplikasi-pos-fitur' => "Aplikasi POS {$cityName} dengan {$featureName}",
            'software-kasir-fitur' => "Software Kasir {$cityName} — {$featureName}",
            // City+Industry combo
            'aplikasi-pos-industri' => "Aplikasi POS untuk {$industryName} di {$cityName}",
        ];

        $data['seoMeta']['title'] = $titleMap[$pattern] ?? $data['seoMeta']['title'];

        $data['seoMeta']['description'] = $this->generateDescription($pattern, $cityName, $featureName, $industryName);

        $data['seoMeta']['canonical'] = url("/{$pattern}-{$slug}");

        return $data;
    }

    protected function generateDescription(string $pattern, string $city, string $feature, string $industry): string
    {
        $base = "{$this->brand} — source code aplikasi Point of Sale / POS lengkap. ";
        $cta = "Beli source code, lifetime update. WA {$this->waNumber}. {$this->sourceCodePrice}.";

        if ($city) {
            return "{$base}Cari aplikasi POS di {$city}? Solusi kasir modern untuk toko, minimarket, retail. Multi-outlet, inventori, laporan keuangan. {$cta}";
        }
        if ($industry) {
            return "{$base}Aplikasi POS khusus {$industry}. Kelola transaksi, stok bahan, laporan penjualan. {$cta}";
        }
        if ($feature) {
            return "{$base}Aplikasi kasir dengan {$feature} — solusi lengkap untuk bisnis Anda. {$cta}";
        }

        return "{$base}Kelola transaksi, stok, pelanggan, laporan dalam satu dashboard. Multi-outlet support. {$cta}";
    }

    // ================================================================
    // PATTERN GENERATORS
    // ================================================================

    protected function cityPatterns(array $cities): array
    {
        $patterns = [
            'aplikasi-pos', 'software-kasir', 'sistem-kasir', 'program-kasir',
            'aplikasi-toko', 'aplikasi-kasir', 'point-of-sale', 'pos-system',
            'aplikasi-pos-murah', 'aplikasi-pos-terbaik', 'aplikasi-pos-terjangkau',
            'rekomendasi-aplikasi-pos', 'review-aplikasi-pos', 'cara-memilih-pos',
            'tips-memilih-kasir', 'daftar-aplikasi-pos',
            'pos-cloud-vs-lokal', 'aplikasi-pos-vs-manual',
        ];

        $pages = [];
        foreach ($cities as $city) {
            $slug = Str::slug($city['name']);
            foreach ($patterns as $pattern) {
                $pages[] = [
                    'url' => "/{$pattern}-{$slug}",
                    'title' => "Aplikasi POS " . ucwords($city['name']),
                    'type' => 'city-pseo',
                    'priority' => '0.7',
                    'lastmod' => now()->toAtomString(),
                ];
            }
        }
        return $pages;
    }

    protected function sourceCodePatterns(array $cities): array
    {
        $patterns = [
            'source-code-pos', 'beli-aplikasi-pos', 'beli-source-code',
            'harga-source-code', 'jual-aplikasi-kasir',
        ];

        $pages = [];
        foreach ($cities as $city) {
            $slug = Str::slug($city['name']);
            foreach ($patterns as $pattern) {
                $pages[] = [
                    'url' => "/{$pattern}-{$slug}",
                    'title' => "Source Code POS " . ucwords($city['name']),
                    'type' => 'source-code-pseo',
                    'priority' => '0.9',
                    'lastmod' => now()->toAtomString(),
                ];
            }
        }
        return $pages;
    }

    protected function cityFeatureCombos(array $cities, array $features): array
    {
        $patterns = ['aplikasi-pos-fitur', 'software-kasir-fitur', 'source-code-pos-fitur', 'beli-aplikasi-pos-fitur'];
        $pages = [];

        foreach ($cities as $city) {
            $citySlug = Str::slug($city['name']);
            foreach ($features as $feature) {
                $featSlug = Str::slug($feature['name']);
                foreach ($patterns as $pattern) {
                    $pages[] = [
                        'url' => "/{$pattern}-{$citySlug}-{$featSlug}",
                        'title' => "POS {$city['name']} — {$feature['name']}",
                        'type' => 'city-feature',
                        'priority' => '0.6',
                        'lastmod' => now()->toAtomString(),
                    ];
                }
            }
        }
        return $pages;
    }

    protected function cityIndustryCombos(array $cities, array $industries): array
    {
        $patterns = ['aplikasi-pos-industri', 'software-kasir-industri'];
        $pages = [];

        foreach ($cities as $city) {
            $citySlug = Str::slug($city['name']);
            foreach ($industries as $industry) {
                $indSlug = Str::slug($industry['name']);
                foreach ($patterns as $pattern) {
                    $pages[] = [
                        'url' => "/{$pattern}-{$citySlug}-{$indSlug}",
                        'title' => "POS {$industry['name']} di {$city['name']}",
                        'type' => 'city-industry',
                        'priority' => '0.6',
                        'lastmod' => now()->toAtomString(),
                    ];
                }
            }
        }
        return $pages;
    }

    protected function industryPatterns(array $industries): array
    {
        $patterns = ['aplikasi-pos-untuk', 'software-kasir-untuk', 'pos-untuk'];
        $pages = [];

        foreach ($industries as $industry) {
            $slug = Str::slug($industry['name']);
            foreach ($patterns as $pattern) {
                $pages[] = [
                    'url' => "/{$pattern}-{$slug}",
                    'title' => "POS {$industry['name']}",
                    'type' => 'industry',
                    'priority' => '0.7',
                    'lastmod' => now()->toAtomString(),
                ];
            }
        }
        return $pages;
    }

    protected function featurePatterns(array $features): array
    {
        $patterns = ['aplikasi-kasir-dengan', 'pos-dengan', 'sistem-kasir-dengan'];
        $pages = [];

        foreach ($features as $feature) {
            $slug = Str::slug($feature['name']);
            foreach ($patterns as $pattern) {
                $pages[] = [
                    'url' => "/{$pattern}-{$slug}",
                    'title' => "POS dengan {$feature['name']}",
                    'type' => 'feature',
                    'priority' => '0.7',
                    'lastmod' => now()->toAtomString(),
                ];
            }
        }
        return $pages;
    }

    protected function staticPatterns(): array
    {
        $pages = [];

        $pages[] = ['url' => '/', 'title' => 'POS Retail — Solusi Point of Sale', 'type' => 'home', 'priority' => '1.0', 'lastmod' => now()->toAtomString()];
        $pages[] = ['url' => '/docs', 'title' => 'Dokumentasi POS Retail', 'type' => 'docs', 'priority' => '0.8', 'lastmod' => now()->toAtomString()];
        $pages[] = ['url' => '/pos', 'title' => 'POS Kasir — Point of Sale', 'type' => 'pos', 'priority' => '0.7', 'lastmod' => now()->toAtomString()];
        $pages[] = ['url' => '/beli-aplikasi-pos', 'title' => 'Beli Aplikasi POS — Source Code Point of Sale', 'type' => 'landing', 'priority' => '1.0', 'lastmod' => now()->toAtomString()];
        $pages[] = ['url' => '/beli-source-code-pos', 'title' => 'Beli Source Code POS Retail — Full Source Code', 'type' => 'landing', 'priority' => '1.0', 'lastmod' => now()->toAtomString()];
        $pages[] = ['url' => '/jual-source-code-pos', 'title' => 'Jual Source Code Aplikasi POS — Siap Pakai', 'type' => 'landing', 'priority' => '1.0', 'lastmod' => now()->toAtomString()];
        $pages[] = ['url' => '/harga-source-code-pos', 'title' => 'Harga Source Code POS Retail — ' . $this->sourceCodePrice, 'type' => 'landing', 'priority' => '0.9', 'lastmod' => now()->toAtomString()];
        $pages[] = ['url' => '/source-code-aplikasi-pos', 'title' => 'Source Code Aplikasi POS — Point of Sale', 'type' => 'landing', 'priority' => '0.9', 'lastmod' => now()->toAtomString()];
        $pages[] = ['url' => '/sitemap', 'title' => 'Sitemap', 'type' => 'sitemap', 'priority' => '0.5', 'lastmod' => now()->toAtomString()];

        return $pages;
    }

    // ================================================================
    // DATA SOURCES
    // ================================================================

    public function indonesianCities(): array
    {
        return [
            ['name' => 'Jakarta'], ['name' => 'Surabaya'], ['name' => 'Bandung'], ['name' => 'Medan'],
            ['name' => 'Semarang'], ['name' => 'Makassar'], ['name' => 'Palembang'], ['name' => 'Tangerang'],
            ['name' => 'Bekasi'], ['name' => 'Depok'], ['name' => 'Bogor'], ['name' => 'Yogyakarta'],
            ['name' => 'Malang'], ['name' => 'Solo'], ['name' => 'Denpasar'], ['name' => 'Batam'],
            ['name' => 'Pekanbaru'], ['name' => 'Padang'], ['name' => 'Balikpapan'], ['name' => 'Banjarmasin'],
            ['name' => 'Samarinda'], ['name' => 'Manado'], ['name' => 'Pontianak'], ['name' => 'Ambon'],
            ['name' => 'Mataram'], ['name' => 'Kupang'], ['name' => 'Jambi'], ['name' => 'Bandar Lampung'],
            ['name' => 'Cirebon'], ['name' => 'Tasikmalaya'], ['name' => 'Sukabumi'], ['name' => 'Cimahi'],
            ['name' => 'Cilegon'], ['name' => 'Serang'], ['name' => 'Tegal'], ['name' => 'Pekalongan'],
            ['name' => 'Purwokerto'], ['name' => 'Magelang'], ['name' => 'Klaten'], ['name' => 'Salatiga'],
            ['name' => 'Kediri'], ['name' => 'Madiun'], ['name' => 'Blitar'], ['name' => 'Probolinggo'],
            ['name' => 'Pasuruan'], ['name' => 'Mojokerto'], ['name' => 'Jember'], ['name' => 'Banyuwangi'],
            ['name' => 'Singaraja'], ['name' => 'Tabanan'], ['name' => 'Gresik'], ['name' => 'Sidoarjo'],
            ['name' => 'Palu'], ['name' => 'Kendari'], ['name' => 'Gorontalo'], ['name' => 'Ternate'],
            ['name' => 'Jayapura'], ['name' => 'Sorong'], ['name' => 'Manokwari'], ['name' => 'Merauke'],
            ['name' => 'Banda Aceh'], ['name' => 'Lhokseumawe'], ['name' => 'Langsa'], ['name' => 'Binjai'],
            ['name' => 'Pematangsiantar'], ['name' => 'Tebing Tinggi'], ['name' => 'Dumai'], ['name' => 'Bukittinggi'],
            ['name' => 'Payakumbuh'], ['name' => 'Sawahlunto'], ['name' => 'Lubuklinggau'], ['name' => 'Prabumulih'],
            ['name' => 'Pangkal Pinang'], ['name' => 'Tanjung Pinang'], ['name' => 'Bontang'], ['name' => 'Tarakan'],
            ['name' => 'Banjarbaru'], ['name' => 'Palangkaraya'], ['name' => 'Bitung'], ['name' => 'Tomohon'],
            ['name' => 'Kotamobagu'], ['name' => 'Bau Bau'], ['name' => 'Parepare'], ['name' => 'Palopo'],
            ['name' => 'Bima'], ['name' => 'Sumbawa'], ['name' => 'Ende'], ['name' => 'Maumere'],
            ['name' => 'Ruteng'], ['name' => 'Atambua'], ['name' => 'Kefamenanu'], ['name' => 'Soe'],
            ['name' => 'Waingapu'], ['name' => 'Labuan Bajo'], ['name' => 'Tual'], ['name' => 'Masohi'],
            ['name' => 'Namlea'], ['name' => 'Raja Ampat'], ['name' => 'Biak'], ['name' => 'Timika'],
            ['name' => 'Wamena'], ['name' => 'Nabire'], ['name' => 'Serui'], ['name' => 'Tanjung Selor'],
            ['name' => 'Singkawang'], ['name' => 'Mempawah'], ['name' => 'Ketapang'], ['name' => 'Putussibau'],
            ['name' => 'Sintang'], ['name' => 'Sanggau'], ['name' => 'Martapura'], ['name' => 'Barabai'],
            ['name' => 'Kandangan'], ['name' => 'Amuntai'], ['name' => 'Tanjung'], ['name' => 'Rantau'],
            ['name' => 'Marabahan'], ['name' => 'Pelaihari'], ['name' => 'Kotabaru'], ['name' => 'Tenggarong'],
            ['name' => 'Sangatta'], ['name' => 'Tanjung Redeb'], ['name' => 'Nunukan'], ['name' => 'Malinau'],
            ['name' => 'Tana Toraja'], ['name' => 'Enrekang'], ['name' => 'Sinjai'], ['name' => 'Bulukumba'],
            ['name' => 'Bantaeng'], ['name' => 'Jeneponto'], ['name' => 'Takalar'], ['name' => 'Maros'],
            ['name' => 'Pangkep'], ['name' => 'Barru'], ['name' => 'Soppeng'], ['name' => 'Wajo'],
            ['name' => 'Sidenreng'], ['name' => 'Pinrang'], ['name' => 'Luwu'], ['name' => 'Kolaka'],
            ['name' => 'Raha'], ['name' => 'Unaaha'], ['name' => 'Andolo'], ['name' => 'Wangi Wangi'],
            ['name' => 'Rangkasbitung'], ['name' => 'Pandeglang'], ['name' => 'Garut'], ['name' => 'Cianjur'],
            ['name' => 'Sumedang'], ['name' => 'Indramayu'], ['name' => 'Majalengka'], ['name' => 'Kuningan'],
            ['name' => 'Subang'], ['name' => 'Purwakarta'], ['name' => 'Karawang'], ['name' => 'Ciamis'],
            ['name' => 'Banjar'], ['name' => 'Banjarnegara'], ['name' => 'Banyumas'], ['name' => 'Cilacap'],
            ['name' => 'Kebumen'], ['name' => 'Purbalingga'], ['name' => 'Wonosobo'], ['name' => 'Temanggung'],
            ['name' => 'Boyolali'], ['name' => 'Sragen'], ['name' => 'Karanganyar'], ['name' => 'Wonogiri'],
            ['name' => 'Sukoharjo'], ['name' => 'Kudus'], ['name' => 'Jepara'], ['name' => 'Pati'],
            ['name' => 'Rembang'], ['name' => 'Blora'], ['name' => 'Grobogan'], ['name' => 'Demak'],
            ['name' => 'Batang'], ['name' => 'Kendal'], ['name' => 'Brebes'], ['name' => 'Pemalang'],
            ['name' => 'Bojonegoro'], ['name' => 'Tuban'], ['name' => 'Lamongan'], ['name' => 'Nganjuk'],
            ['name' => 'Ponorogo'], ['name' => 'Trenggalek'], ['name' => 'Tulungagung'], ['name' => 'Lumajang'],
            ['name' => 'Bondowoso'], ['name' => 'Situbondo'], ['name' => 'Sumenep'], ['name' => 'Pamekasan'],
            ['name' => 'Sampang'], ['name' => 'Bangkalan'], ['name' => 'Magetan'], ['name' => 'Ngawi'],
            ['name' => 'Pacitan'], ['name' => 'Agam'], ['name' => 'Solok'], ['name' => 'Tanah Datar'],
            ['name' => 'Batusangkar'], ['name' => 'Pariaman'], ['name' => 'Painan'], ['name' => 'Muara Bungo'],
            ['name' => 'Bangka'], ['name' => 'Sungailiat'], ['name' => 'Mentok'], ['name' => 'Toboali'],
            ['name' => 'Muntok'], ['name' => 'Belinyu'], ['name' => 'Koba'], ['name' => 'Manggar'],
            ['name' => 'Tanjung Pandan'], ['name' => 'Muara Enim'], ['name' => 'Baturaja'], ['name' => 'Lahat'],
            ['name' => 'Pagar Alam'], ['name' => 'Kayu Agung'], ['name' => 'Indralaya'], ['name' => 'Martapura OKU'],
            ['name' => 'Muara Dua'], ['name' => 'Curup'], ['name' => 'Kepahiang'], ['name' => 'Arga Makmur'],
            ['name' => 'Mukomuko'], ['name' => 'Manna'], ['name' => 'Tais'], ['name' => 'Kotabumi'],
            ['name' => 'Kalianda'], ['name' => 'Liwa'], ['name' => 'Blambangan Umpu'], ['name' => 'Menggala'],
            ['name' => 'Gedong Tataan'], ['name' => 'Pringsewu'], ['name' => 'Metro'], ['name' => 'Sukadana'],
            ['name' => 'Gunung Sugih'], ['name' => 'Way Kanan'], ['name' => 'Tulang Bawang'], ['name' => 'Mesuji'],
            ['name' => 'Rantau Prapat'], ['name' => 'Kisaran'], ['name' => 'Sibolga'], ['name' => 'Padangsidimpuan'],
            ['name' => 'Gunungsitoli'], ['name' => 'Sidikalang'], ['name' => 'Balige'], ['name' => 'Tarutung'],
            ['name' => 'Panyabungan'], ['name' => 'Padang Lawas'], ['name' => 'Tapaktuan'], ['name' => 'Meulaboh'],
            ['name' => 'Sigli'], ['name' => 'Takengon'], ['name' => 'Blangkejeren'], ['name' => 'Kutacane'],
            ['name' => 'Subulussalam'], ['name' => 'Sinabang'], ['name' => 'Calang'], ['name' => 'Idi'],
        ];
    }

    public function posFeatures(): array
    {
        return [
            ['name' => 'Multi-Outlet'], ['name' => 'Inventori Real-Time'], ['name' => 'Laporan Keuangan'],
            ['name' => 'Barcode Scanner'], ['name' => 'Payment Gateway'], ['name' => 'QRIS'],
            ['name' => 'Loyalitas Pelanggan'], ['name' => 'Stok Opname'], ['name' => 'Transfer Stok'],
            ['name' => 'Approval Workflow'], ['name' => 'Multi-User'], ['name' => 'Akses Role-Based'],
            ['name' => 'Dashboard Analitik'], ['name' => 'Export Excel'], ['name' => 'Export PDF'],
            ['name' => 'Notifikasi Stok'], ['name' => 'Manajemen Supplier'], ['name' => 'Purchase Order'],
            ['name' => 'Customer Portal'], ['name' => 'API Integrasi'], ['name' => 'Cloud Sync'],
            ['name' => 'Offline Mode'], ['name' => 'Cetak Struk'], ['name' => 'Shift Kasir'],
            ['name' => 'Audit Trail'], ['name' => 'Multi-Gudang'], ['name' => 'Diskon Otomatis'],
            ['name' => 'Promo Bundle'], ['name' => 'Membership Tier'], ['name' => 'Kitchen Display'],
            ['name' => 'Table Management'], ['name' => 'Raw Material Tracking'], ['name' => 'Recipe Management'],
            ['name' => 'Hold & Recall Cart'], ['name' => 'Split Payment'], ['name' => 'Installment'],
            ['name' => 'Return & Refund'], ['name' => 'Anti-Fraud'], ['name' => 'Backup Otomatis'],
            ['name' => 'Multi-Cabang'], ['name' => 'Laporan Pajak'], ['name' => 'Grafik Penjualan'],
            ['name' => 'Top Produk'], ['name' => 'Cash Flow'], ['name' => 'Profit & Loss'],
            ['name' => 'WhatsApp Gateway'], ['name' => 'Email Notifikasi'], ['name' => 'SMS Gateway'],
            ['name' => 'Dark Mode'], ['name' => 'Mobile Responsive'], ['name' => 'Localization IDR'],
        ];
    }

    public function industries(): array
    {
        return [
            ['name' => 'Retail'], ['name' => 'Minimarket'], ['name' => 'Supermarket'],
            ['name' => 'Toko Kelontong'], ['name' => 'Toko Baju'], ['name' => 'Toko Sepatu'],
            ['name' => 'Toko Elektronik'], ['name' => 'Toko HP'], ['name' => 'Toko Buku'],
            ['name' => 'Toko Mainan'], ['name' => 'Toko Furniture'], ['name' => 'Toko Bangunan'],
            ['name' => 'Toko Obat'], ['name' => 'Apotek'], ['name' => 'Toko Kosmetik'],
            ['name' => 'Toko Sembako'], ['name' => 'Toko Alat Tulis'], ['name' => 'Toko Souvenir'],
            ['name' => 'Toko Olahraga'], ['name' => 'Restoran'], ['name' => 'Cafe'],
            ['name' => 'Bakery'], ['name' => 'Coffee Shop'], ['name' => 'Warung Makan'],
            ['name' => 'Kantin'], ['name' => 'Barbershop'], ['name' => 'Laundry'],
            ['name' => 'Bengkel'], ['name' => 'Toko Sparepart'], ['name' => 'Toko Pertanian'],
            ['name' => 'Toko Ikan'], ['name' => 'Toko Daging'], ['name' => 'Toko Buah'],
            ['name' => 'Toko Kue'], ['name' => 'Distributor'], ['name' => 'Grosir'],
            ['name' => 'Agen'], ['name' => 'Reseller'], ['name' => 'Dropshipper'],
            ['name' => 'UMKM'], ['name' => 'Warung Sembako'], ['name' => 'Toko Oleh-Oleh'],
            ['name' => 'Florist'], ['name' => 'Pet Shop'], ['name' => 'Toko Alat Musik'],
            ['name' => 'Toko Aksesoris'], ['name' => 'Toko Jam'], ['name' => 'Optik'],
            ['name' => 'Toko Foto Copy'], ['name' => 'Percetakan'],
        ];
    }

    protected function findCity(string $slug): ?array
    {
        foreach ($this->indonesianCities() as $city) {
            if (Str::slug($city['name']) === $slug || Str::contains($slug, Str::slug($city['name']))) {
                return $city;
            }
        }
        return null;
    }

    protected function findFeature(string $slug): ?array
    {
        foreach ($this->posFeatures() as $feature) {
            if (Str::slug($feature['name']) === $slug || Str::contains($slug, Str::slug($feature['name']))) {
                return $feature;
            }
        }
        return null;
    }

    protected function findIndustry(string $slug): ?array
    {
        foreach ($this->industries() as $industry) {
            if (Str::slug($industry['name']) === $slug || Str::contains($slug, Str::slug($industry['name']))) {
                return $industry;
            }
        }
        return null;
    }
}
