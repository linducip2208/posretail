<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <title>{{ $seoMeta['title'] }}</title>
    <meta name="description" content="{{ $seoMeta['description'] }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $seoMeta['canonical'] }}">
    <meta property="og:title" content="{{ $seoMeta['title'] }}">
    <meta property="og:description" content="{{ $seoMeta['description'] }}">
    <meta property="og:url" content="{{ $seoMeta['canonical'] }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,700" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>body{font-family:Inter,sans-serif}</style>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "{{ $brand }}",
        "applicationCategory": "PointOfSaleApplication",
        "operatingSystem": "Web",
        "description": "{{ $seoMeta['description'] }}",
        "offers": {
            "@type": "Offer",
            "price": "4999000",
            "priceCurrency": "IDR",
            "availability": "https://schema.org/InStock"
        }
    }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">

<nav class="bg-gradient-to-r from-blue-600 to-blue-800 text-white sticky top-0 z-40 shadow-lg">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="font-extrabold text-lg tracking-tight">{{ $brand }}</a>
        <div class="flex gap-4 text-sm font-medium">
            <a href="/" class="hover:text-blue-200 transition">Beranda</a>
            <a href="/docs" class="hover:text-blue-200 transition">Dokumentasi</a>
            <a href="/beli-aplikasi-pos" class="hover:text-blue-200 transition">Beli Source Code</a>
        </div>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-4 py-10">
    {{-- Hero CTA --}}
    <section class="bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 text-white rounded-3xl p-8 md:p-12 mb-10 text-center">
        <h1 class="text-2xl md:text-4xl font-extrabold mb-4 leading-tight">{{ $seoMeta['title'] }}</h1>
        <p class="text-blue-200 text-lg mb-8 max-w-2xl mx-auto">{{ $seoMeta['description'] }}</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="https://wa.me/{{ $waNumber }}?text=Halo%20saya%20tertarik%20beli%20source%20code%20{{ urlencode($brand) }}" target="_blank"
               class="px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-bold text-lg shadow-xl shadow-emerald-500/30 transition hover:-translate-y-0.5">
                Beli Source Code via WhatsApp &rarr;
            </a>
            <a href="/docs" class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white rounded-2xl font-bold text-lg backdrop-blur transition border border-white/20">
                Lihat Dokumentasi
            </a>
        </div>
    </section>

    {{-- Content --}}
    <div class="grid md:grid-cols-3 gap-8">
        {{-- Main Content --}}
        <div class="md:col-span-2 space-y-6">
            <article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    @if($city)
                        Aplikasi POS di {{ $city['name'] }} — Point of Sale Terbaik
                    @elseif($industry)
                        Solusi POS untuk Bisnis {{ $industry['name'] }}
                    @elseif($feature)
                        Aplikasi POS dengan {{ $feature['name'] }}
                    @else
                        {{ $brand }} — Source Code Aplikasi POS
                    @endif
                </h2>

                <div class="prose max-w-none text-gray-700 leading-relaxed space-y-4 text-[15px]">
                    <p>
                        <strong>{{ $brand }}</strong> adalah aplikasi Point of Sale (POS) / sistem kasir modern yang dirancang khusus untuk bisnis retail di Indonesia. 
                        @if($city)
                            Jika Anda mencari aplikasi POS di <strong>{{ $city['name'] }}</strong> dan sekitarnya, {{ $brand }} adalah solusi tepat. 
                        @endif
                        Aplikasi ini dilengkapi dengan fitur lengkap untuk mengelola transaksi penjualan, inventori, pembelian, laporan keuangan, hingga program loyalitas pelanggan.
                    </p>

                    <h3 class="text-lg font-bold text-gray-900">Kenapa Memilih {{ $brand }}?</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold mt-0.5">&check;</span> <span><strong>Multi-Outlet</strong> — Kelola banyak cabang toko dalam satu dashboard terintegrasi.</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold mt-0.5">&check;</span> <span><strong>Inventori Real-Time</strong> — Pantau stok semua outlet secara real-time, dapatkan notifikasi stok rendah.</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold mt-0.5">&check;</span> <span><strong>Payment Gateway Dinamis</strong> — Dukung QRIS, GoPay, OVO, transfer bank, dan kartu kredit.</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold mt-0.5">&check;</span> <span><strong>Laporan Lengkap</strong> — Laporan penjualan, keuangan (P&L), dan stok dengan chart interaktif.</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold mt-0.5">&check;</span> <span><strong>Loyalitas Pelanggan</strong> — Program poin, membership tier, dan diskon otomatis.</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold mt-0.5">&check;</span> <span><strong>API v1</strong> — Integrasikan dengan aplikasi mobile atau third-party.</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold mt-0.5">&check;</span> <span><strong>Role-Based Access</strong> — Owner, manager, admin, kasir, gudang — masing-masing dengan hak akses berbeda.</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold mt-0.5">&check;</span> <span><strong>Anti-Fraud</strong> — Audit trail, approval threshold, cash drawer reconciliation.</span></li>
                    </ul>

                    <h3 class="text-lg font-bold text-gray-900">Spesifikasi Teknis</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start gap-2"><span class="text-blue-500 font-bold mt-0.5">&rarr;</span> <span>Dibangun dengan <strong>Laravel</strong> + <strong>FilamentPHP</strong> + <strong>TailwindCSS</strong></span></li>
                        <li class="flex items-start gap-2"><span class="text-blue-500 font-bold mt-0.5">&rarr;</span> <span>52 tabel database, 30+ admin resources, 3 halaman laporan</span></li>
                        <li class="flex items-start gap-2"><span class="text-blue-500 font-bold mt-0.5">&rarr;</span> <span>Customer portal, Blog module, PSEO directory</span></li>
                        <li class="flex items-start gap-2"><span class="text-blue-500 font-bold mt-0.5">&rarr;</span> <span>REST API v1 dengan Sanctum authentication</span></li>
                        <li class="flex items-start gap-2"><span class="text-blue-500 font-bold mt-0.5">&rarr;</span> <span>Dynamic provider system — tidak hardcode payment gateway</span></li>
                    </ul>

                    @if($city)
                        <p>
                            <strong>Butuh aplikasi POS di {{ $city['name'] }}?</strong> {{ $brand }} sudah digunakan oleh berbagai toko retail di Indonesia. 
                            Source code bisa dibeli dan di-custom sesuai kebutuhan. <strong>Lifetime update, 6 bulan support.</strong>
                        </p>
                    @endif
                </div>
            </article>

            {{-- Related Pages --}}
            @if(!empty($relatedPages))
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-4">Halaman Terkait</h3>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($relatedPages as $rp)
                        <a href="{{ $rp['url'] }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline py-1">{{ $rp['title'] }}</a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar — Source Code CTA + City List --}}
        <div class="space-y-6">
            <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white rounded-2xl shadow-lg p-6 sticky top-20">
                <div class="text-4xl mb-3">💻</div>
                <h3 class="font-bold text-lg mb-2">Beli Source Code</h3>
                <p class="text-blue-100 text-sm mb-4">Full source code <strong>{{ $brand }}</strong> — aplikasi POS / Point of Sale siap pakai. 1&times; bayar, lifetime.</p>
                <div class="text-2xl font-extrabold mb-4">{{ $sourceCodePrice }}</div>
                <a href="https://wa.me/{{ $waNumber }}?text=Halo%20saya%20mau%20beli%20source%20code%20{{ urlencode($brand) }}" target="_blank"
                   class="block w-full text-center py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold transition shadow-lg shadow-emerald-500/30">
                    Chat WhatsApp Sekarang
                </a>
                <p class="text-blue-200 text-xs mt-3 text-center">Respon cepat · Demo lengkap · Garansi 6 bulan</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h4 class="font-semibold text-gray-900 mb-3 text-sm uppercase tracking-wider">Kota Lainnya</h4>
                <div class="space-y-1">
                    @foreach($topCities as $c)
                        <a href="/aplikasi-pos-{{ \Illuminate\Support\Str::slug($c['name']) }}" class="block px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition">
                            Aplikasi POS {{ $c['name'] }}
                        </a>
                    @endforeach
                </div>
                <a href="/sitemap" class="block mt-3 text-xs text-blue-600 font-semibold hover:underline">Lihat Semua Kota &rarr;</a>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h4 class="font-semibold text-gray-900 mb-3 text-sm uppercase tracking-wider">Fitur Unggulan</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($features as $f)
                        <a href="/aplikasi-kasir-dengan-{{ \Illuminate\Support\Str::slug($f['name']) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-blue-100 text-gray-600 hover:text-blue-700 rounded-full text-xs font-medium transition">
                            {{ $f['name'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="bg-gray-900 text-gray-400 py-10 mt-12">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid md:grid-cols-3 gap-6 text-sm mb-8">
            <div>
                <h4 class="font-bold text-white mb-2">{{ $brand }}</h4>
                <p>Source code aplikasi POS / Point of Sale untuk bisnis retail Indonesia.</p>
            </div>
            <div>
                <h4 class="font-bold text-white mb-2">Link</h4>
                <ul class="space-y-1">
                    <li><a href="/beli-aplikasi-pos" class="hover:text-white transition">Beli Source Code</a></li>
                    <li><a href="/docs" class="hover:text-white transition">Dokumentasi</a></li>
                    <li><a href="/sitemap" class="hover:text-white transition">Sitemap</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-white mb-2">Kontak</h4>
                <p>WA: {{ substr($waNumber, 2) }}</p>
            </div>
        </div>
        <div class="border-t border-gray-800 pt-6 text-center text-xs">
            &copy; {{ date('Y') }} {{ $brand }}. Source code aplikasi Point of Sale.
        </div>
    </div>
</footer>
</body>
</html>
