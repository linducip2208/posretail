<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\ProgrammaticSeoController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Portal\AuthController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\ApiDocsController;
use App\Http\Controllers\BarcodeController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin');
    }
    return view('marketing');
})->name('home');

Route::get('/docs', [DocsController::class, 'index'])->name('docs');
Route::get('/docs/api', [ApiDocsController::class, 'index'])->name('docs.api');

Route::get('/pos', [PosController::class, 'index'])->name('pos')->middleware('auth');

Route::get('/barcode/{code}', [BarcodeController::class, 'show'])->name('barcode.image');

Route::get('/pos/display', function () {
    $appName = \App\Models\SystemSetting::getAppName();
    return view('pos.customer-display', compact('appName'));
})->name('pos.display');

Route::get('/menu/{outlet}', function (\App\Models\Outlet $outlet) {
    $table = request('table') ? \App\Models\TableResto::find(request('table')) : null;
    return view('pos.digital-menu', compact('outlet', 'table'));
})->name('menu.digital');

Route::get('/api/pos/products', [PosController::class, 'products']);
Route::get('/api/pos/barcode/{barcode}', [PosController::class, 'barcode']);
Route::get('/api/pos/display', [PosController::class, 'display']);
Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout')->middleware('auth');
Route::get('/admin/orders/{id}/receipt', [PosController::class, 'receipt'])->name('orders.receipt')->middleware('auth');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap-static.xml', [SitemapController::class, 'staticSitemap'])->name('sitemap.static');
Route::get('/sitemap-blog.xml', [SitemapController::class, 'blogSitemap'])->name('sitemap.blog');
Route::get('/sitemap-pseo-{chunk}.xml', [SitemapController::class, 'pseoSitemap'])->name('sitemap.pseo')->where('chunk', '[0-9]+');
Route::get('/sitemap', [SitemapController::class, 'html'])->name('sitemap.html');

Route::get('/export/laporan/penjualan', [ReportExportController::class, 'sales'])->name('export.sales')->middleware('auth');
Route::get('/export/laporan/keuangan', [ReportExportController::class, 'financial'])->name('export.financial')->middleware('auth');
Route::get('/export/laporan/stok', [ReportExportController::class, 'stock'])->name('export.stock')->middleware('auth');
Route::get('/export/laporan/penjualan/pdf', [ReportExportController::class, 'salesPdf'])->name('export.sales.pdf')->middleware('auth');
Route::get('/export/laporan/keuangan/pdf', [ReportExportController::class, 'financialPdf'])->name('export.financial.pdf')->middleware('auth');
Route::get('/export/laporan/stok/pdf', [ReportExportController::class, 'stockPdf'])->name('export.stock.pdf')->middleware('auth');

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
        Route::get('/order/{id}/invoice', [PortalController::class, 'downloadInvoice'])->name('order.invoice');
        Route::post('/order/{id}/upload-proof', [PortalController::class, 'uploadProof'])->name('order.upload-proof');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

// Public blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/feed.xml', [BlogController::class, 'feed'])->name('blog.feed');
Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// FAQ & Contact
Route::get('/faq', fn () => view('pages.faq'))->name('faq');
Route::get('/contact', fn () => view('pages.contact'))->name('contact');

// PSEO — Source code landing pages (high priority)
Route::get('/beli-aplikasi-pos', [ProgrammaticSeoController::class, 'staticPage'])->defaults('slug', 'beli-aplikasi-pos');
Route::get('/beli-source-code-pos', [ProgrammaticSeoController::class, 'staticPage'])->defaults('slug', 'beli-source-code-pos');
Route::get('/jual-source-code-pos', [ProgrammaticSeoController::class, 'staticPage'])->defaults('slug', 'jual-source-code-pos');
Route::get('/harga-source-code-pos', [ProgrammaticSeoController::class, 'staticPage'])->defaults('slug', 'harga-source-code-pos');
Route::get('/source-code-aplikasi-pos', [ProgrammaticSeoController::class, 'staticPage'])->defaults('slug', 'source-code-aplikasi-pos');

// PSEO — Comparison, alternatives & best-of (standar wajib)
Route::get('/bandingkan/{slug}', [ProgrammaticSeoController::class, 'compare'])
    ->where('slug', '[a-z0-9-]+-vs-[a-z0-9-]+')->name('pseo.compare');
Route::get('/compare/{slug}', [ProgrammaticSeoController::class, 'compare'])
    ->where('slug', '[a-z0-9-]+-vs-[a-z0-9-]+')->name('pseo.compare-en');
Route::get('/alternatif-{slug}', [ProgrammaticSeoController::class, 'alternatives'])->name('pseo.alternatives-to');
Route::get('/alternatives-to-{slug}', [ProgrammaticSeoController::class, 'alternatives'])->name('pseo.alternatives-to-en');
Route::get('/best-{slug}', [ProgrammaticSeoController::class, 'bestCategory'])->name('pseo.best-category');
Route::get('/aplikasi-pos-terbaik-untuk-{slug}', [ProgrammaticSeoController::class, 'bestCategory'])->name('pseo.best-category-id');

// PSEO — City-based patterns
Route::get('/aplikasi-pos-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'aplikasi-pos')->name('pseo.city');
Route::get('/software-kasir-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'software-kasir');
Route::get('/sistem-kasir-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'sistem-kasir');
Route::get('/program-kasir-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'program-kasir');
Route::get('/aplikasi-toko-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'aplikasi-toko');
Route::get('/aplikasi-kasir-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'aplikasi-kasir');
Route::get('/point-of-sale-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'point-of-sale');
Route::get('/pos-system-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'pos-system');

// PSEO — Source code buying intent
Route::get('/source-code-pos-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'source-code-pos');
Route::get('/beli-aplikasi-pos-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'beli-aplikasi-pos');
Route::get('/beli-source-code-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'beli-source-code');
Route::get('/harga-source-code-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'harga-source-code');
Route::get('/jual-aplikasi-kasir-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'jual-aplikasi-kasir');
Route::get('/aplikasi-pos-murah-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'aplikasi-pos-murah');
Route::get('/aplikasi-pos-terbaik-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'aplikasi-pos-terbaik');
Route::get('/aplikasi-pos-terjangkau-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'aplikasi-pos-terjangkau');
Route::get('/rekomendasi-aplikasi-pos-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'rekomendasi-aplikasi-pos');
Route::get('/review-aplikasi-pos-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'review-aplikasi-pos');
Route::get('/cara-memilih-pos-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'cara-memilih-pos');
Route::get('/tips-memilih-kasir-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'tips-memilih-kasir');
Route::get('/daftar-aplikasi-pos-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'daftar-aplikasi-pos');
Route::get('/pos-cloud-vs-lokal-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'pos-cloud-vs-lokal');
Route::get('/aplikasi-pos-vs-manual-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'aplikasi-pos-vs-manual');

// PSEO — Industry patterns
Route::get('/aplikasi-pos-untuk-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'aplikasi-pos-untuk');
Route::get('/software-kasir-untuk-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'software-kasir-untuk');
Route::get('/pos-untuk-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'pos-untuk');

// PSEO — Feature patterns
Route::get('/aplikasi-kasir-dengan-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'aplikasi-kasir-dengan');
Route::get('/pos-dengan-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'pos-dengan');
Route::get('/sistem-kasir-dengan-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'sistem-kasir-dengan');

// PSEO — City+Feature combo (2-part slug)
Route::get('/aplikasi-pos-fitur-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'aplikasi-pos-fitur')
    ->where('slug', '[a-z0-9-]+-[a-z0-9-]+');
Route::get('/software-kasir-fitur-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'software-kasir-fitur')
    ->where('slug', '[a-z0-9-]+-[a-z0-9-]+');
Route::get('/source-code-pos-fitur-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'source-code-pos-fitur')
    ->where('slug', '[a-z0-9-]+-[a-z0-9-]+');
Route::get('/beli-aplikasi-pos-fitur-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'beli-aplikasi-pos-fitur')
    ->where('slug', '[a-z0-9-]+-[a-z0-9-]+');

// PSEO — City+Industry combo (2-part slug)
Route::get('/aplikasi-pos-industri-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'aplikasi-pos-industri')
    ->where('slug', '[a-z0-9-]+-[a-z0-9-]+');
Route::get('/software-kasir-industri-{slug}', [ProgrammaticSeoController::class, 'page'])->defaults('pattern', 'software-kasir-industri')
    ->where('slug', '[a-z0-9-]+-[a-z0-9-]+');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Allow: /\$\n";
    $content .= "Allow: /portal\n";
    $content .= "Allow: /docs\n";
    $content .= "Allow: /blog\n";
    $content .= "Allow: /faq\n";
    $content .= "Allow: /contact\n";
    $content .= "Allow: /beli-\n";
    $content .= "Allow: /jual-\n";
    $content .= "Allow: /harga-\n";
    $content .= "Allow: /source-code-\n";
    $content .= "Allow: /best-\n";
    $content .= "Allow: /bandingkan/\n";
    $content .= "Allow: /compare/\n";
    $content .= "Allow: /alternatif-\n";
    $content .= "Allow: /alternatives-to-\n";
    $content .= "Allow: /aplikasi-pos-\n";
    $content .= "Allow: /aplikasi-kasir-\n";
    $content .= "Allow: /aplikasi-toko-\n";
    $content .= "Allow: /software-kasir-\n";
    $content .= "Allow: /sistem-kasir-\n";
    $content .= "Allow: /program-kasir-\n";
    $content .= "Allow: /point-of-sale-\n";
    $content .= "Allow: /pos-\n";
    $content .= "Allow: /daftar-\n";
    $content .= "Allow: /rekomendasi-\n";
    $content .= "Allow: /tips-\n";
    $content .= "Allow: /cara-\n";
    $content .= "Allow: /review-\n";
    $content .= "Allow: /marketing/\n";
    $content .= "Disallow: /admin\n";
    $content .= "Disallow: /api\n";
    $content .= "Disallow: /__pair\n";
    $content .= "Disallow: /webhooks\n";
    $content .= "Sitemap: " . rtrim(config('app.url'), '/') . "/sitemap.xml\n";

    return response($content, 200)
        ->header('Content-Type', 'text/plain; charset=utf-8');
})->name('robots');

Route::get('/tax-invoice/{taxInvoice}/pdf', function (\App\Models\TaxInvoice $taxInvoice) {
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.tax-invoice', compact('taxInvoice'));
    return $pdf->download('faktur-pajak-' . $taxInvoice->invoice_number . '.pdf');
})->name('tax-invoice.pdf')->middleware('auth');

Route::redirect('/login', '/admin/login')->name('login');

require base_path('routes/pair-routes.php');
