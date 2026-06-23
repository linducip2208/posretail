<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Outlet;
use Illuminate\Support\Facades\Cache;

class PseoService
{
    /**
     * Generate ALL PSEO page URLs for sitemap and internal linking.
     * Returns array of ['url' => '/path', 'title' => '...', 'type' => '...', 'priority' => '0.8']
     */
    public function getAllPages(): array
    {
        return Cache::remember('pseo.all_pages', 86400, function () {
            $pages = [];

            $pages = array_merge($pages, $this->productPages());
            $pages = array_merge($pages, $this->categoryPages());
            $pages = array_merge($pages, $this->brandPages());
            $pages = array_merge($pages, $this->bestCategoryPages());
            $pages = array_merge($pages, $this->bestCategoryYearPages());
            $pages = array_merge($pages, $this->alternativesPages());
            $pages = array_merge($pages, $this->comparePages());
            $pages = array_merge($pages, $this->daftarHargaPages());
            $pages = array_merge($pages, $this->tipsMemilihPages());
            $pages = array_merge($pages, $this->outletCityPages());
            $pages = array_merge($pages, $this->staticPages());

            return $pages;
        });
    }

    private function productPages(): array
    {
        return Product::where('active', true)->get()->map(fn($p) => [
            'url' => "/produk/{$p->slug}",
            'title' => "{$p->name} — Harga, Spesifikasi & Review | POS Retail",
            'description' => "Beli {$p->name} harga Rp " . number_format($p->selling_price, 0, ',', '.') . ". Produk asli, garansi, tersedia di outlet kami.",
            'type' => 'product',
            'priority' => '0.9',
            'lastmod' => $p->updated_at->toAtomString(),
        ])->toArray();
    }

    private function categoryPages(): array
    {
        return Category::where('active', true)->get()->map(fn($c) => [
            'url' => "/kategori/{$c->slug}",
            'title' => "Koleksi {$c->name} Terlengkap — Harga Terbaik | POS Retail",
            'description' => "Jelajahi koleksi {$c->name} terlengkap. Mulai dari harga ekonomis hingga premium. Ready stock, bisa beli grosir!",
            'type' => 'category',
            'priority' => '0.8',
            'lastmod' => $c->updated_at->toAtomString(),
        ])->toArray();
    }

    private function brandPages(): array
    {
        return Brand::where('active', true)->get()->map(fn($b) => [
            'url' => "/brand/{$b->slug}",
            'title' => "Produk {$b->name} Original — Harga Resmi & Terpercaya | POS Retail",
            'description' => "Produk {$b->name} 100% original. Harga resmi, garansi distributor. Tersedia di semua outlet kami.",
            'type' => 'brand',
            'priority' => '0.7',
            'lastmod' => $b->updated_at->toAtomString(),
        ])->toArray();
    }

    private function bestCategoryPages(): array
    {
        return Category::where('active', true)->get()->map(fn($c) => [
            'url' => "/best-{$c->slug}",
            'title' => "10+ {$c->name} Terbaik — Rekomendasi Produk Berkualitas",
            'description' => "Rekomendasi {$c->name} terbaik dan paling laris. Produk original, harga kompetitif. Diskon khusus member!",
            'type' => 'best-category',
            'priority' => '0.9',
            'lastmod' => now()->toAtomString(),
        ])->toArray();
    }

    private function bestCategoryYearPages(): array
    {
        $pages = [];
        $categories = Category::where('active', true)->get();
        $years = range(2024, 2026);

        foreach ($categories as $c) {
            foreach ($years as $year) {
                $pages[] = [
                    'url' => "/best-{$c->slug}-{$year}",
                    'title' => "{$c->name} Terbaik {$year} — Pilihan Tepat untuk Kebutuhan Anda",
                    'description' => "Rekomendasi {$c->name} terbaik tahun {$year}. Produk original, harga terbaik. Tersedia di outlet kami!",
                    'type' => 'best-category-year',
                    'priority' => '0.8',
                    'lastmod' => now()->toAtomString(),
                ];
            }
        }
        return $pages;
    }

    private function alternativesPages(): array
    {
        return Product::where('active', true)->get()->map(fn($p) => [
            'url' => "/alternatives-to-{$p->slug}",
            'title' => "10 Alternatif {$p->name} — Produk Serupa Harga Lebih Murah",
            'description' => "Cari alternatif {$p->name}? Lihat produk serupa yang lebih terjangkau, kualitas setara.",
            'type' => 'alternatives',
            'priority' => '0.8',
            'lastmod' => now()->toAtomString(),
        ])->toArray();
    }

    private function comparePages(): array
    {
        $pages = [];
        $products = Product::where('active', true)
            ->orderByDesc('current_stock')
            ->take(30)
            ->get();

        foreach ($products as $i => $a) {
            foreach ($products as $j => $b) {
                if ($j <= $i) continue;
                if ($a->category_id !== $b->category_id) continue; // Only compare same category
                $pages[] = [
                    'url' => "/compare/{$a->slug}-vs-{$b->slug}",
                    'title' => "{$a->name} vs {$b->name} — Perbandingan Lengkap Harga & Spesifikasi",
                    'description' => "Bandingkan {$a->name} dan {$b->name}: harga, spesifikasi, kelebihan, dan mana yang lebih cocok.",
                    'type' => 'compare',
                    'priority' => '0.8',
                    'lastmod' => now()->toAtomString(),
                ];
            }
        }
        return $pages;
    }

    private function daftarHargaPages(): array
    {
        return Category::where('active', true)->get()->map(fn($c) => [
            'url' => "/daftar-harga-{$c->slug}",
            'title' => "Daftar Harga {$c->name} Terbaru Hari Ini — Update Setiap Hari | POS Retail",
            'description' => "Daftar harga {$c->name} terbaru dan terlengkap. Update harga setiap hari. Cek harga eceran, grosir, dan member!",
            'type' => 'price-list',
            'priority' => '0.9',
            'lastmod' => now()->toAtomString(),
        ])->toArray();
    }

    private function tipsMemilihPages(): array
    {
        $templates = [
            'tips-memilih' => 'Tips Memilih',
            'cara-merawat' => 'Cara Merawat',
            'kelebihan-kekurangan' => 'Kelebihan & Kekurangan',
            'perbandingan-harga' => 'Perbandingan Harga',
            'review-terbaru' => 'Review Terbaru',
        ];

        $pages = [];
        $categories = Category::where('active', true)->get();

        foreach ($categories as $c) {
            foreach ($templates as $prefix => $label) {
                $pages[] = [
                    'url' => "/{$prefix}-{$c->slug}",
                    'title' => "{$label} {$c->name} — Panduan Lengkap | POS Retail",
                    'description' => "{$label} {$c->name} terlengkap. Panduan memilih produk terbaik sesuai kebutuhan dan budget Anda.",
                    'type' => 'guide',
                    'priority' => '0.7',
                    'lastmod' => now()->toAtomString(),
                ];
            }
        }
        return $pages;
    }

    private function outletCityPages(): array
    {
        $pages = [];
        $outlets = Outlet::where('active', true)->get();

        foreach ($outlets as $o) {
            // Extract city from address
            $city = $this->extractCity($o->address);
            if ($city) {
                $citySlug = \Illuminate\Support\Str::slug($city);
                $pages[] = [
                    'url' => "/toko-{$citySlug}",
                    'title' => "Toko Retail di {$city} — Produk Lengkap Harga Terbaik | POS Retail",
                    'description' => "Kunjungi toko kami di {$city}. Produk lengkap, harga terbaik, bisa beli grosir. Buka setiap hari!",
                    'type' => 'store-location',
                    'priority' => '0.6',
                    'lastmod' => now()->toAtomString(),
                ];
            }
        }
        return $pages;
    }

    private function staticPages(): array
    {
        return [
            ['url' => '/', 'title' => 'POS Retail — Solusi Kasir Modern', 'type' => 'home', 'priority' => '1.0', 'lastmod' => now()->toAtomString(), 'description' => ''],
            ['url' => '/docs', 'title' => 'Dokumentasi POS Retail', 'type' => 'docs', 'priority' => '0.8', 'lastmod' => now()->toAtomString(), 'description' => ''],
            ['url' => '/pos', 'title' => 'POS Kasir — Point of Sale', 'type' => 'pos', 'priority' => '0.7', 'lastmod' => now()->toAtomString(), 'description' => ''],
            ['url' => '/sitemap', 'title' => 'Sitemap — Daftar Semua Halaman', 'type' => 'sitemap', 'priority' => '0.5', 'lastmod' => now()->toAtomString(), 'description' => ''],
        ];
    }

    private function extractCity(?string $address): ?string
    {
        if (!$address) return null;
        $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Semarang', 'Yogyakarta', 'Makassar', 'Palembang', 'Tangerang', 'Bekasi', 'Depok', 'Bogor'];
        foreach ($cities as $city) {
            if (stripos($address, $city) !== false) return $city;
        }
        return null;
    }
}
