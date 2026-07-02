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

    public function getSitemapChunkCount(int $chunkSize): int
    {
        return (int) ceil($this->totalPatternCount() / $chunkSize);
    }

    public function totalPatternCount(): int
    {
        $cities = count($this->indonesianCities());
        $features = count($this->posFeatures());
        $industries = count($this->industries());
        $competitors = 12;

        $count = 0;

        $count += $cities * 18;
        $count += $cities * 5;
        $count += $features * 3;
        $count += $industries * 3;
        $count += $industries * 2;
        $count += $competitors * 5;
        $count += 12;

        $count += $cities * min($features, 20) * 4;
        $count += $cities * $industries * 2;

        $count += $cities * min($features, 15) * $industries * 1;
        $count += $cities * min($features, 10) * min($features, 10) * 1;

        $count += $cities * ($cities - 1);

        return $count;
    }

    public function getSitemapChunk(int $offset, int $limit): array
    {
        $cities = $this->indonesianCities();
        $features = $this->posFeatures();
        $industries = $this->industries();
        $competitors = [
            'moka', 'pawoon', 'olsera', 'majoo', 'qasir', 'kasir-pintar',
            'iseller', 'olsera-pos', 'esb-pos', 'gobiz', 'square', 'loyverse',
        ];

        $pages = [];
        $current = 0;

        $cursor = &$current;
        $result = &$pages;
        $chunkLimit = $offset + $limit;

        $emit = function (string $url, string $type, string $priority, bool $indexable = true) use (&$current, &$result, $offset, $chunkLimit): bool {
            if ($current >= $chunkLimit) return false;
            $current++;
            if ($current <= $offset) return true;
            $result[] = [
                'url' => $url,
                'type' => $type,
                'priority' => $priority,
                'freq' => 'monthly',
                'lastmod' => date('Y-m-d'),
            ];
            return true;
        };

        $emitMany = function (string $prefix, string $suffix, string $type, string $priority) use ($emit): bool {
            return $emit("{$prefix}{$suffix}", $type, $priority);
        };

        $cityPatterns = [
            'aplikasi-pos', 'software-kasir', 'sistem-kasir', 'program-kasir',
            'aplikasi-toko', 'aplikasi-kasir', 'point-of-sale', 'pos-system',
            'aplikasi-pos-murah', 'aplikasi-pos-terbaik', 'aplikasi-pos-terjangkau',
            'rekomendasi-aplikasi-pos', 'review-aplikasi-pos', 'cara-memilih-pos',
            'tips-memilih-kasir', 'daftar-aplikasi-pos',
            'pos-cloud-vs-lokal', 'aplikasi-pos-vs-manual',
        ];

        foreach ($cities as $city) {
            $slug = Str::slug($city['name']);
            foreach ($cityPatterns as $pat) {
                if (! $emit("/{$pat}-{$slug}", 'city', '0.7')) return $pages;
            }
        }

        $scPatterns = ['source-code-pos', 'beli-aplikasi-pos', 'beli-source-code', 'harga-source-code', 'jual-aplikasi-kasir'];
        foreach ($cities as $city) {
            $slug = Str::slug($city['name']);
            foreach ($scPatterns as $pat) {
                if (! $emit("/{$pat}-{$slug}", 'source-code', '0.9')) return $pages;
            }
        }

        foreach ($features as $feat) {
            $slug = Str::slug($feat['name']);
            foreach (['aplikasi-kasir-dengan', 'pos-dengan', 'sistem-kasir-dengan'] as $pat) {
                if (! $emit("/{$pat}-{$slug}", 'feature', '0.7')) return $pages;
            }
        }

        foreach ($industries as $ind) {
            $slug = Str::slug($ind['name']);
            foreach (['aplikasi-pos-untuk', 'software-kasir-untuk', 'pos-untuk'] as $pat) {
                if (! $emit("/{$pat}-{$slug}", 'industry', '0.7')) return $pages;
            }
        }

        foreach ($industries as $ind) {
            $slug = Str::slug($ind['name']);
            if (! $emit("/best-{$slug}", 'best-of', '0.8')) return $pages;
            if (! $emit("/aplikasi-pos-terbaik-untuk-{$slug}", 'best-of', '0.7')) return $pages;
        }

        foreach ($competitors as $c) {
            $cSlug = Str::slug($c);
            if (! $emit("/alternatif-{$cSlug}", 'alternatives', '0.8')) return $pages;
            if (! $emit("/alternatives-to-{$cSlug}", 'alternatives', '0.6')) return $pages;
            if (! $emit("/bandingkan/pos-retail-vs-{$cSlug}", 'compare', '0.8')) return $pages;
            if (! $emit("/compare/pos-retail-vs-{$cSlug}", 'compare', '0.6')) return $pages;
            if (! $emit("/bandingkan/{$cSlug}-vs-pos-retail", 'compare', '0.7')) return $pages;
        }

        $staticPages = [
            ['url' => '/', 'type' => 'home', 'priority' => '1.0'],
            ['url' => '/docs', 'type' => 'docs', 'priority' => '0.8'],
            ['url' => '/pos', 'type' => 'pos', 'priority' => '0.7'],
            ['url' => '/blog', 'type' => 'blog', 'priority' => '0.9'],
            ['url' => '/faq', 'type' => 'static', 'priority' => '0.6'],
            ['url' => '/contact', 'type' => 'static', 'priority' => '0.6'],
            ['url' => '/beli-aplikasi-pos', 'type' => 'landing', 'priority' => '1.0'],
            ['url' => '/beli-source-code-pos', 'type' => 'landing', 'priority' => '1.0'],
            ['url' => '/jual-source-code-pos', 'type' => 'landing', 'priority' => '1.0'],
            ['url' => '/harga-source-code-pos', 'type' => 'landing', 'priority' => '0.9'],
            ['url' => '/source-code-aplikasi-pos', 'type' => 'landing', 'priority' => '0.9'],
            ['url' => '/sitemap', 'type' => 'sitemap', 'priority' => '0.5'],
        ];
        foreach ($staticPages as $s) {
            if (! $emit($s['url'], $s['type'], $s['priority'])) return $pages;
        }

        $subFeatures = array_slice($features, 0, 20);
        $comboPatterns = ['aplikasi-pos-fitur', 'software-kasir-fitur', 'source-code-pos-fitur', 'beli-aplikasi-pos-fitur'];
        foreach ($cities as $city) {
            $citySlug = Str::slug($city['name']);
            foreach ($subFeatures as $feat) {
                $featSlug = Str::slug($feat['name']);
                foreach ($comboPatterns as $pat) {
                    if (! $emit("/{$pat}-{$citySlug}-{$featSlug}", 'city-feature', '0.6')) return $pages;
                }
            }
        }

        $indComboPatterns = ['aplikasi-pos-industri', 'software-kasir-industri'];
        foreach ($cities as $city) {
            $citySlug = Str::slug($city['name']);
            foreach ($industries as $ind) {
                $indSlug = Str::slug($ind['name']);
                foreach ($indComboPatterns as $pat) {
                    if (! $emit("/{$pat}-{$citySlug}-{$indSlug}", 'city-industry', '0.6')) return $pages;
                }
            }
        }

        $topFeatures2 = array_slice($features, 0, 15);
        foreach ($cities as $city) {
            $citySlug = Str::slug($city['name']);
            foreach ($topFeatures2 as $feat) {
                $featSlug = Str::slug($feat['name']);
                foreach ($industries as $ind) {
                    $indSlug = Str::slug($ind['name']);
                    if (! $emit("/aplikasi-pos-{$citySlug}-{$featSlug}-{$indSlug}", 'city-feat-industry', '0.5')) return $pages;
                }
            }
        }

        $topFeatures3 = array_slice($features, 0, 10);
        foreach ($cities as $city) {
            $citySlug = Str::slug($city['name']);
            foreach ($topFeatures3 as $f1) {
                $f1Slug = Str::slug($f1['name']);
                foreach ($topFeatures3 as $f2) {
                    if ($f1['name'] === $f2['name']) continue;
                    $f2Slug = Str::slug($f2['name']);
                    if (! $emit("/aplikasi-pos-{$citySlug}-{$f1Slug}-{$f2Slug}", 'city-dual-feature', '0.5')) return $pages;
                }
            }
        }

        return $pages;
    }

    public function getAllPages(): array
    {
        return Cache::remember('pseo.all_pages', 86400, function () {
            return $this->getSitemapChunk(0, 5000);
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
            'aplikasi-pos-untuk' => "Aplikasi POS untuk {$industryName} — Solusi Kasir",
            'software-kasir-untuk' => "Software Kasir untuk {$industryName} — POS Retail",
            'pos-untuk' => "POS untuk {$industryName} — Aplikasi Point of Sale",
            'aplikasi-kasir-dengan' => "Aplikasi Kasir dengan {$featureName} — POS Retail",
            'pos-dengan' => "POS dengan {$featureName} — Point of Sale Lengkap",
            'sistem-kasir-dengan' => "Sistem Kasir dengan {$featureName} — POS Modern",
            'aplikasi-pos-fitur' => "Aplikasi POS {$cityName} dengan {$featureName}",
            'software-kasir-fitur' => "Software Kasir {$cityName} — {$featureName}",
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

        if ($city && $industry) {
            return "{$base}Solusi POS khusus {$industry} di {$city}. Kelola transaksi, stok, laporan keuangan. {$cta}";
        }
        if ($city && $feature) {
            return "{$base}Cari aplikasi POS {$city} dengan {$feature}? Solusi kasir modern untuk toko, minimarket. {$cta}";
        }
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
}
