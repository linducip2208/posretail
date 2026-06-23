<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use Illuminate\View\View;

class ProgrammaticSeoController extends Controller
{
    public function bestCategory(string $slug): View
    {
        $category = Category::where('slug', $slug)->where('active', true)->firstOrFail();
        $products = Product::where('category_id', $category->id)->where('active', true)
            ->orderByDesc('current_stock')->take(10)->get();
        $seoMeta = ['title' => "10+ {$category->name} Terbaik — Produk Berkualitas Harga Terjangkau", 'description' => "Rekomendasi {$category->name} terbaik dan paling laris. Diskon khusus member!", 'canonical' => url("/best-{$slug}")];
        return view('pseo.best-category', compact('category', 'products', 'seoMeta'));
    }

    public function bestCategoryYear(string $slug, string $year): View
    {
        $category = Category::where('slug', $slug)->where('active', true)->firstOrFail();
        $products = Product::where('category_id', $category->id)->where('active', true)->take(10)->get();
        $seoMeta = ['title' => "{$category->name} Terbaik {$year} — Pilihan Tepat", 'description' => "Rekomendasi {$category->name} terbaik tahun {$year}. Harga terbaik!", 'canonical' => url("/best-{$slug}-{$year}")];
        return view('pseo.best-category', compact('category', 'products', 'seoMeta'));
    }

    public function alternativesTo(string $slug): View
    {
        $product = Product::where('slug', $slug)->where('active', true)->firstOrFail();
        $alternatives = Product::where('category_id', $product->category_id)->where('active', true)->where('id', '!=', $product->id)->take(10)->get();
        $seoMeta = ['title' => "10 Alternatif {$product->name} — Harga Lebih Murah", 'description' => "Cari alternatif {$product->name}? Produk serupa, kualitas setara, harga lebih terjangkau.", 'canonical' => url("/alternatives-to-{$slug}")];
        return view('pseo.alternatives-to', compact('product', 'alternatives', 'seoMeta'));
    }

    public function compare(string $a, string $b): View
    {
        $slugs = explode('-vs-', "{$a}-vs-{$b}");
        $productA = Product::where('slug', $slugs[0] ?? '')->where('active', true)->firstOrFail();
        $productB = Product::where('slug', $slugs[1] ?? '')->where('active', true)->firstOrFail();
        $seoMeta = ['title' => "{$productA->name} vs {$productB->name} — Perbandingan Lengkap", 'description' => "Bandingkan {$productA->name} dan {$productB->name}: harga, spesifikasi, mana yang lebih cocok.", 'canonical' => url("/compare/{$slugs[0]}-vs-{$slugs[1]}")];
        return view('pseo.compare', compact('productA', 'productB', 'seoMeta'));
    }

    public function productDetail(string $slug): View
    {
        $product = Product::with(['category', 'brand', 'unit'])
            ->where('slug', $slug)->where('active', true)->firstOrFail();
        $related = Product::where('category_id', $product->category_id)->where('id', '!=', $product->id)->where('active', true)->take(6)->get();
        $seoMeta = ['title' => "{$product->name} — Harga Rp " . number_format($product->selling_price, 0, ',', '.') . " | POS Retail", 'description' => "Beli {$product->name} harga terbaik. " . ($product->description ?? 'Produk original, ready stock.'), 'canonical' => url("/produk/{$slug}")];
        return view('pseo.product-detail', compact('product', 'related', 'seoMeta'));
    }

    public function categoryPage(string $slug): View
    {
        $category = Category::where('slug', $slug)->where('active', true)->firstOrFail();
        $products = Product::where('category_id', $category->id)->where('active', true)->paginate(24);
        $seoMeta = ['title' => "Koleksi {$category->name} Terlengkap — Harga Terbaik | POS Retail", 'description' => "Jelajahi koleksi {$category->name} terlengkap. Ready stock, harga grosir tersedia!", 'canonical' => url("/kategori/{$slug}")];
        return view('pseo.category-page', compact('category', 'products', 'seoMeta'));
    }

    public function brandPage(string $slug): View
    {
        $brand = Brand::where('slug', $slug)->where('active', true)->firstOrFail();
        $products = Product::where('brand_id', $brand->id)->where('active', true)->paginate(24);
        $seoMeta = ['title' => "Produk {$brand->name} Original — Harga Resmi | POS Retail", 'description' => "Produk {$brand->name} 100% original. Harga resmi, garansi distributor.", 'canonical' => url("/brand/{$slug}")];
        return view('pseo.brand-page', compact('brand', 'products', 'seoMeta'));
    }

    public function priceList(string $slug): View
    {
        $category = Category::where('slug', $slug)->where('active', true)->firstOrFail();
        $products = Product::where('category_id', $category->id)->where('active', true)->orderBy('selling_price')->get();
        $seoMeta = ['title' => "Daftar Harga {$category->name} Terbaru Hari Ini | POS Retail", 'description' => "Daftar harga {$category->name} terlengkap. Update setiap hari. Cek harga eceran, grosir, dan member!", 'canonical' => url("/daftar-harga-{$slug}")];
        return view('pseo.price-list', compact('category', 'products', 'seoMeta'));
    }

    public function guide(string $slug, string $type = 'tips'): View
    {
        // Extract prefix from URL
        $prefix = request()->segment(1);
        $labelMap = [
            'tips-memilih' => 'Tips Memilih',
            'cara-merawat' => 'Cara Merawat',
            'kelebihan-kekurangan' => 'Kelebihan & Kekurangan',
            'perbandingan-harga' => 'Perbandingan Harga',
            'review-terbaru' => 'Review Terbaru',
        ];
        $label = $labelMap[$prefix] ?? 'Panduan';
        $category = Category::where('slug', $slug)->where('active', true)->firstOrFail();
        $products = Product::where('category_id', $category->id)->where('active', true)->take(6)->get();
        $seoMeta = ['title' => "{$label} {$category->name} — Panduan Lengkap | POS Retail", 'description' => "{$label} {$category->name} terlengkap. Panduan memilih produk terbaik sesuai budget.", 'canonical' => url("/{$prefix}-{$slug}")];
        return view('pseo.guide', compact('category', 'products', 'label', 'seoMeta'));
    }

    public function storeLocation(string $city): View
    {
        $cityName = ucwords(str_replace('-', ' ', $city));
        $outlets = Outlet::where('active', true)->where('address', 'like', "%{$cityName}%")->get();
        if ($outlets->isEmpty()) {
            $outlets = Outlet::where('active', true)->get();
        }
        $seoMeta = ['title' => "Toko Retail di {$cityName} — Produk Lengkap Harga Terbaik | POS Retail", 'description' => "Kunjungi toko kami di {$cityName}. Produk lengkap, harga terbaik, bisa beli grosir.", 'canonical' => url("/toko-{$city}")];
        return view('pseo.store-location', compact('cityName', 'outlets', 'seoMeta'));
    }
}
