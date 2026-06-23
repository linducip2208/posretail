<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProgrammaticSeoController;
use App\Http\Controllers\Portal\AuthController;
use App\Http\Controllers\Portal\PortalController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin');
    }
    return view('marketing');
})->name('home');

Route::get('/docs', [DocsController::class, 'index'])->name('docs');

Route::get('/pos', [PosController::class, 'index'])->name('pos');

Route::get('/api/pos/products', [PosController::class, 'products']);
Route::get('/api/pos/barcode/{barcode}', [PosController::class, 'barcode']);
Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout')->middleware('auth');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap', [SitemapController::class, 'html'])->name('sitemap.html');

Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [AuthController::class, 'register']);
    });

    Route::middleware('auth:customer')->group(function () {
        Route::get('/', [PortalController::class, 'index'])->name('index');
        Route::post('/lookup', [PortalController::class, 'lookup'])->name('lookup');
        Route::get('/order/{id}', [PortalController::class, 'orderDetail'])->name('order');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Allow: /\$\n";
    $content .= "Allow: /portal\n";
    $content .= "Allow: /docs\n";
    $content .= "Allow: /marketing/\n";
    $content .= "Allow: /best-\n";
    $content .= "Allow: /alternatives-to-\n";
    $content .= "Allow: /compare/\n";
    $content .= "Disallow: /admin\n";
    $content .= "Disallow: /api\n";
    $content .= "Disallow: /__pair\n";
    $content .= "Disallow: /webhooks\n";
    $content .= "Sitemap: /sitemap.xml\n";

    return response($content, 200)
        ->header('Content-Type', 'text/plain; charset=utf-8');
})->name('robots');

Route::get('/best-{slug}', [ProgrammaticSeoController::class, 'bestCategory'])->name('pseo.best-category');
Route::get('/best-{slug}-{year}', [ProgrammaticSeoController::class, 'bestCategoryYear'])->name('pseo.best-category-year');
Route::get('/alternatives-to-{slug}', [ProgrammaticSeoController::class, 'alternativesTo'])->name('pseo.alternatives-to');
Route::get('/compare/{a}-vs-{b}', [ProgrammaticSeoController::class, 'compare'])
    ->where(['a' => '[a-z0-9-]+', 'b' => '[a-z0-9-]+'])
    ->name('pseo.compare');
Route::get('/produk/{slug}', [ProgrammaticSeoController::class, 'productDetail'])->name('pseo.product');
Route::get('/kategori/{slug}', [ProgrammaticSeoController::class, 'categoryPage'])->name('pseo.category');
Route::get('/brand/{slug}', [ProgrammaticSeoController::class, 'brandPage'])->name('pseo.brand');
Route::get('/daftar-harga-{slug}', [ProgrammaticSeoController::class, 'priceList'])->name('pseo.price-list');
Route::get('/tips-memilih-{slug}', [ProgrammaticSeoController::class, 'guide'])->name('pseo.guide');
Route::get('/cara-merawat-{slug}', [ProgrammaticSeoController::class, 'guide'])->name('pseo.care');
Route::get('/kelebihan-kekurangan-{slug}', [ProgrammaticSeoController::class, 'guide'])->name('pseo.pros-cons');
Route::get('/perbandingan-harga-{slug}', [ProgrammaticSeoController::class, 'guide'])->name('pseo.price-compare');
Route::get('/review-terbaru-{slug}', [ProgrammaticSeoController::class, 'guide'])->name('pseo.review');
Route::get('/toko-{city}', [ProgrammaticSeoController::class, 'storeLocation'])->name('pseo.store');

require base_path('routes/pair-routes.php');
