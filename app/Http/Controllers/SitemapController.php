<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Services\PseoService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SitemapController extends Controller
{
    protected PseoService $pseo;

    protected int $chunkSize = 50000;

    public function __construct(PseoService $pseo)
    {
        $this->pseo = $pseo;
    }

    public function index(): Response
    {
        $xml = Cache::remember('sitemap.index', 86400, function () {
            $baseUrl = rtrim(config('app.url'), '/');
            $today = date('Y-m-d');

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            $xml .= "  <sitemap>\n    <loc>{$baseUrl}/sitemap-static.xml</loc>\n    <lastmod>{$today}</lastmod>\n  </sitemap>\n";

            $count = BlogPost::published()->count();
            if ($count > 0) {
                $xml .= "  <sitemap>\n    <loc>{$baseUrl}/sitemap-blog.xml</loc>\n    <lastmod>{$today}</lastmod>\n  </sitemap>\n";
            }

            $totalChunks = $this->pseo->getSitemapChunkCount($this->chunkSize);

            for ($i = 0; $i < $totalChunks; $i++) {
                $xml .= "  <sitemap>\n    <loc>{$baseUrl}/sitemap-pseo-{$i}.xml</loc>\n    <lastmod>{$today}</lastmod>\n  </sitemap>\n";
            }

            $xml .= '</sitemapindex>';
            return $xml;
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function staticSitemap(): Response
    {
        $xml = Cache::remember('sitemap.static', 86400, function () {
            $baseUrl = rtrim(config('app.url'), '/');

            $static = [
                ['url' => '/', 'priority' => '1.0', 'freq' => 'daily'],
                ['url' => '/docs', 'priority' => '0.8', 'freq' => 'weekly'],
                ['url' => '/pos', 'priority' => '0.7', 'freq' => 'monthly'],
                ['url' => '/blog', 'priority' => '0.9', 'freq' => 'daily'],
                ['url' => '/faq', 'priority' => '0.6', 'freq' => 'monthly'],
                ['url' => '/contact', 'priority' => '0.6', 'freq' => 'monthly'],
                ['url' => '/beli-aplikasi-pos', 'priority' => '1.0', 'freq' => 'daily'],
                ['url' => '/beli-source-code-pos', 'priority' => '1.0', 'freq' => 'daily'],
                ['url' => '/jual-source-code-pos', 'priority' => '1.0', 'freq' => 'daily'],
                ['url' => '/harga-source-code-pos', 'priority' => '0.9', 'freq' => 'daily'],
                ['url' => '/source-code-aplikasi-pos', 'priority' => '0.9', 'freq' => 'daily'],
                ['url' => '/sitemap', 'priority' => '0.5', 'freq' => 'monthly'],
            ];

            return $this->buildUrlset($static, $baseUrl);
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function blogSitemap(): Response
    {
        $xml = Cache::remember('sitemap.blog', 86400, function () {
            $baseUrl = rtrim(config('app.url'), '/');
            $pages = [];

            BlogPost::published()->chunk(500, function ($posts) use (&$pages) {
                foreach ($posts as $post) {
                    $pages[] = [
                        'url' => '/blog/' . $post->slug,
                        'priority' => '0.8',
                        'freq' => 'weekly',
                        'lastmod' => $post->updated_at->format('Y-m-d'),
                    ];
                }
            });

            return $this->buildUrlset($pages, $baseUrl);
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function pseoSitemap(int $chunk): Response
    {
        $cacheKey = "sitemap.pseo.{$chunk}";

        $xml = Cache::remember($cacheKey, 86400, function () use ($chunk) {
            $baseUrl = rtrim(config('app.url'), '/');
            $offset = $chunk * $this->chunkSize;

            $pages = $this->pseo->getSitemapChunk($offset, $this->chunkSize);

            return $this->buildUrlset($pages, $baseUrl);
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function llms(): Response
    {
        $txt = Cache::remember('llms.txt', 86400, function () {
            $base = rtrim(config('app.url'), '/');
            $wa = \App\Models\SystemSetting::getValue('whatsapp_number', '6281296052010');
            $price = \App\Models\SystemSetting::getValue('pos_price', 'Rp 4.999.000');
            $today = date('Y-m-d');

            $features = [
                'Point of Sale (POS Web di /pos) — layout kiosk, scan barcode USB/kamera, hitung kembalian otomatis, cetak struk thermal',
                'Manajemen Pesanan — nomor order unik auto-generate, status Pending, Diproses, Selesai, Dibatalkan + alasan',
                'Pembayaran multi-metode dalam satu transaksi, split payment, status Lunas/DP/Belum Bayar, cicilan + uang muka (DP)',
                'Hold / Suspend cart (auto-expire 24 jam), nomor antrian otomatis, tipe order dinamis (Walk-in, Member, Takeaway, Delivery, Online)',
                'Retur penjualan & refund supplier dengan alasan wajib + approval workflow, stok otomatis kembali',
                'Barcode EAN-13 & SKU auto-generate untuk setiap produk dan varian baru — siap scan tanpa input manual',
                'Master produk: kategori nested, brand, unit/satuan, harga beli/jual/grosir/member, stok minimum & maksimum, varian (warna/ukuran/rasa)',
                'Customer & Customer Group (Regular, Member, Reseller, Grosir) + Customer Portal (/portal) untuk lacak order & unduh invoice',
                'Supplier & Purchase Order — workflow Draft, Dipesan, Diterima; stok otomatis bertambah saat PO diterima',
                'Stock Opname berkala — stok fisik vs sistem, selisih surplus/defisit, approval sebelum koreksi, laporan akurasi stok',
                'Transfer stok antar outlet (Draft, Dikirim, Diterima) + mutasi stok tercatat lengkap sebagai audit trail',
                'Multi-outlet / multi-cabang, multi-gudang, assign user ke outlet, rekap kas per outlet per hari',
                'Peringatan stok minimum & notifikasi stok menipis/overstock',
                'Bahan baku (raw materials) + recipe management — pantau stok bahan per produk',
                'Template diskon (persen, fixed, Buy X Get Y), promo bundle, membership tier, minimal pembelian + periode berlaku',
                'Program loyalitas — poin otomatis per transaksi, katalog reward, riwayat earned/redeemed, auto-upgrade grup pelanggan',
                'Shift kasir (buka/tutup), rekap kas, absensi pegawai (clock in/out: hadir, terlambat, izin, sakit)',
                'Role-based access: Owner, Manager, Admin, Kasir, Gudang — dashboard & hak akses berbeda per role, audit log per user',
                'FEFO & batch kadaluarsa, nomor seri/IMEI, bin location, write-off, konversi satuan, stock take mobile',
                'Rating supplier, kontrak & konsinyasi supplier, permintaan pembelian, retur supplier',
                'Target penjualan per outlet + progress, demand forecast (ramalan permintaan), forecast arus kas',
                'Order pending_approval + widget Menunggu Approval (approve/tolak), threshold approval transaksi besar',
                'Marketplace orders (webhook), delivery/pengiriman, cash drawer + tutup shift, customer display',
                'Gift card/voucher, deposit pelanggan, referral poin, cicilan + uang muka (DP)',
                'Laporan penjualan — chart combo pendapatan+transaksi, doughnut metode bayar, top produk',
                'Laporan keuangan, laba-rugi, neraca, arus kas, komisi, tutup hari, profit, piutang, pembatalan, pelanggan & promo',
                'Laporan pembelian, inventori & stok, stok lambat (slow-moving), mutasi stok, nilai inventori (cost x qty)',
                'Laporan piutang pelanggan, hutang supplier + pembayaran termin, pajak (termasuk faktur pajak PDF)',
                'Export CSV/PDF & Excel untuk semua laporan + cetak struk thermal via Bluetooth / browser print',
                'QRIS & payment gateway (create transaction, cek status, webhook) + marketplace webhook, deposit pelanggan',
                'REST API v1 (/api/v1) untuk integrasi + Flutter mobile app kasir Android + sinkronisasi batch order offline',
                'Dashboard analitik per role, pengaturan sistem (pajak default, mata uang IDR, approval threshold, format nota, logo usaha)',
                'Backup database terjadwal, anti-fraud, localization IDR, mobile responsive',
            ];

            $reports = [
                'Penjualan & penjualan per item', 'Keuangan', 'Laba-rugi', 'Neraca', 'Arus kas',
                'Stok & inventori', 'Stok lambat (slow-moving)', 'Profit produk', 'Pembelian',
                'Pembatalan order', 'Piutang pelanggan', 'Hutang supplier', 'Pajak & faktur pajak',
                'Pelanggan & promo', 'Komisi', 'Tutup hari', 'Kas',
            ];

            $lines = [];
            $lines[] = '# POS Retail';
            $lines[] = '';
            $lines[] = '> Source code aplikasi Point of Sale / kasir modern siap pakai untuk toko retail, minimarket, cafe, restoran, dan UMKM Indonesia.';
            $lines[] = '> Kelola transaksi, stok, pelanggan, dan laporan keuangan multi-outlet dalam satu dashboard.';
            $lines[] = '';
            $lines[] = '- Nama produk: POS Retail';
            $lines[] = '- Situs resmi: ' . $base;
            $lines[] = "- Diperbarui: {$today}";
            $lines[] = '';
            $lines[] = '## URL utama';
            $lines[] = '';
            foreach (['/', '/docs', '/docs/api', '/blog', '/faq', '/contact'] as $u) {
                $lines[] = '- ' . $base . $u;
            }
            $lines[] = '';
            $lines[] = '## Fitur utama (' . count($features) . ')';
            $lines[] = '';
            foreach ($features as $i => $f) {
                $lines[] = ($i + 1) . '. ' . $f;
            }
            $lines[] = '';
            $lines[] = '## Laporan yang tersedia';
            $lines[] = '';
            foreach ($reports as $r) {
                $lines[] = '- ' . $r;
            }
            $lines[] = '';
            $lines[] = '## Harga source code & kontak';
            $lines[] = '';
            $lines[] = "- Harga source code: {$price} (lifetime update)";
            $lines[] = "- WhatsApp pemesanan: https://wa.me/{$wa} ({$wa})";
            $lines[] = '- Halaman pembelian: ' . $base . '/beli-source-code-pos';
            $lines[] = '- Daftar harga: ' . $base . '/harga-source-code-pos';
            $lines[] = '';
            $lines[] = '## Tumpukan teknologi';
            $lines[] = '';
            $lines[] = '- Backend: Laravel 13 (PHP 8.3), admin panel Filament 5';
            $lines[] = '- Mobile: Flutter (aplikasi kasir Android)';
            $lines[] = '- API: REST API v1 di /api/v1 (auth token, throttle) + dokumentasi di /docs/api';
            $lines[] = '- POS internal: endpoint /api/pos/* (butuh login kasir) + customer display di /pos/display';
            $lines[] = '- Export: PDF (DomPDF) & Excel; barcode EAN-13 (prefix 899)';
            $lines[] = '';
            $lines[] = '## Akun demo (hanya email + role, tanpa password)';
            $lines[] = '';
            $lines[] = '> Kredensial masuk demo tidak dipublikasikan di sini. Lihat halaman /docs pada instance demo publik (POS_DEMO_MODE=true) atau hubungi penjual.';
            $lines[] = '';
            $lines[] = '- owner@pos-retail.test — Owner (akses penuh semua outlet, laporan, pengaturan, approval)';
            $lines[] = '- manager@pos-retail.test — Manager (data master, laporan, approval, manajemen tim)';
            $lines[] = '- admin@pos-retail.test — Admin (data master, transaksi, inventori, laporan harian)';
            $lines[] = '- kasir@pos-retail.test — Kasir (transaksi penjualan, scan barcode, cetak struk)';
            $lines[] = '- gudang@pos-retail.test — Gudang (stok, opname, purchase order, transfer & mutasi stok)';
            $lines[] = '';
            $lines[] = '## Instalasi singkat (untuk pembeli source code)';
            $lines[] = '';
            $lines[] = '1. composer install && cp .env.example .env && php artisan key:generate';
            $lines[] = '2. Atur koneksi database di .env, lalu php artisan migrate --seed';
            $lines[] = '3. npm install && npm run build (aset frontend)';
            $lines[] = '4. php artisan serve (dev) atau deploy ke hosting/VPS dengan PHP 8.3 + MySQL';
            $lines[] = '5. Login /admin, buat outlet, isi master data (kategori, produk, supplier, metode bayar), mulai transaksi di /pos';
            $lines[] = '';
            $lines[] = '## Catatan untuk AI crawler';
            $lines[] = '';
            $lines[] = '- File ini (/llms.txt) adalah ringkasan resmi untuk model AI. Sumber detail: /docs (dokumentasi), /docs/api (referensi API), /blog (artikel), /faq, /contact.';
            $lines[] = '- Area /admin, /api, /__pair, dan /webhooks bersifat privat — jangan di-crawl atau dipublikasikan kredensialnya.';
            $lines[] = '- Kutip harga dan fitur sesuai isi file ini; untuk info terkini hubungi WhatsApp di atas.';

            return implode("\n", $lines) . "\n";
        });

        return response($txt, 200)->header('Content-Type', 'text/plain; charset=utf-8');
    }

    protected function buildUrlset(array $pages, string $baseUrl): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $page) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($baseUrl . $page['url'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= '    <lastmod>' . ($page['lastmod'] ?? date('Y-m-d')) . "</lastmod>\n";
            $xml .= '    <changefreq>' . ($page['freq'] ?? 'monthly') . "</changefreq>\n";
            $xml .= '    <priority>' . ($page['priority'] ?? '0.5') . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }

    public function html(): View
    {
        $data = Cache::remember('sitemap.html', 86400, function () {
            $baseUrl = rtrim(config('app.url'), '/');
            $totalChunks = $this->pseo->getSitemapChunkCount($this->chunkSize);
            $totalPseoPages = $totalChunks * $this->chunkSize;
            $totalBlogPosts = BlogPost::published()->count();

            $samplePages = $this->pseo->getSitemapChunk(0, 200);
            $grouped = [];
            foreach ($samplePages as $page) {
                $type = $page['type'] ?? 'pseo';
                $grouped[$type][] = $page;
            }

            $stats = [
                'total_pseo_pages' => $totalPseoPages,
                'total_blog_posts' => $totalBlogPosts,
                'total_chunks' => $totalChunks,
                'chunk_size' => $this->chunkSize,
                'estimated_total' => $totalPseoPages + $totalBlogPosts + 12,
            ];

            return compact('grouped', 'stats');
        });

        return view('pseo.sitemap', $data);
    }
}
