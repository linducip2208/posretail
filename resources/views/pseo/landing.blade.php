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
        "@@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "{{ $brand }}",
        "applicationCategory": "PointOfSaleApplication",
        "operatingSystem": "Web",
        "description": "{{ $seoMeta['description'] }}",
        "offers": {
            "@type": "Offer",
            "price": "4999000",
            "priceCurrency": "IDR"
        }
    }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">

<nav class="bg-gradient-to-r from-blue-600 to-blue-800 text-white sticky top-0 z-40 shadow-lg">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="font-extrabold text-lg tracking-tight">{{ $brand }}</a>
        <a href="/docs" class="text-sm font-medium hover:text-blue-200 transition">Dokumentasi</a>
    </div>
</nav>

<main class="max-w-4xl mx-auto px-4 py-10">
    {{-- Hero --}}
    <section class="bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 text-white rounded-3xl p-10 md:p-16 mb-10 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/20 backdrop-blur rounded-full text-sm font-semibold mb-6">
            <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
            Source Code Tersedia
        </div>
        <h1 class="text-3xl md:text-5xl font-extrabold mb-6 leading-tight">{{ $seoMeta['title'] }}</h1>
        <p class="text-blue-200 text-lg mb-10 max-w-2xl mx-auto leading-relaxed">{{ $seoMeta['description'] }}</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="https://wa.me/{{ $waNumber }}?text=Halo%20saya%20mau%20beli%20source%20code%20{{ urlencode($brand) }}" target="_blank"
               class="px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-bold text-lg shadow-xl shadow-emerald-500/40 transition hover:-translate-y-0.5">
                Beli Source Code via WhatsApp
            </a>
            <a href="/docs" class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white rounded-2xl font-bold text-lg backdrop-blur transition border border-white/20">
                Dokumentasi & Demo
            </a>
        </div>
        <div class="mt-8 flex justify-center gap-2 text-sm text-blue-200">
            <span>WhatsApp:</span>
            <span class="font-bold text-white">+62 {{ substr($waNumber, 2) }}</span>
        </div>
    </section>

    {{-- Content --}}
    <article class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-12 mb-8">
        <h2 class="text-2xl font-extrabold text-gray-900 mb-6">Kenapa Memilih Source Code {{ $brand }}?</h2>

        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl p-5 border border-emerald-200">
                <div class="text-3xl mb-2">💻</div>
                <h3 class="font-bold text-gray-900 mb-1">Full Source Code</h3>
                <p class="text-sm text-gray-600">Dapatkan 100% source code — bukan SaaS, bukan subscription. Self-hosted, data di server Anda sendiri.</p>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-5 border border-blue-200">
                <div class="text-3xl mb-2">⚡</div>
                <h3 class="font-bold text-gray-900 mb-1">Lifetime Update</h3>
                <p class="text-sm text-gray-600">Beli sekali, dapat update selamanya. Tidak ada biaya bulanan atau tahunan.</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-5 border border-purple-200">
                <div class="text-3xl mb-2">🛠️</div>
                <h3 class="font-bold text-gray-900 mb-1">6 Bulan Support</h3>
                <p class="text-sm text-gray-600">Support teknis langsung dari developer. Bantu instalasi, setup, dan troubleshooting.</p>
            </div>
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-2xl p-5 border border-amber-200">
                <div class="text-3xl mb-2">📖</div>
                <h3 class="font-bold text-gray-900 mb-1">Dokumentasi Lengkap</h3>
                <p class="text-sm text-gray-600">Tutorial step-by-step, video demo, screenshot semua fitur. Siap pakai langsung.</p>
            </div>
        </div>

        <h3 class="text-xl font-bold text-gray-900 mb-4">Fitur Lengkap {{ $brand }}</h3>
        <div class="grid md:grid-cols-2 gap-3 text-sm">
            <div class="flex items-start gap-2"><span class="text-emerald-500 font-bold">&check;</span> <span>Multi-Outlet — kelola banyak toko dalam satu dashboard</span></div>
            <div class="flex items-start gap-2"><span class="text-emerald-500 font-bold">&check;</span> <span>Inventori real-time dengan notifikasi stok rendah</span></div>
            <div class="flex items-start gap-2"><span class="text-emerald-500 font-bold">&check;</span> <span>Payment gateway dinamis (QRIS, e-wallet, kartu)</span></div>
            <div class="flex items-start gap-2"><span class="text-emerald-500 font-bold">&check;</span> <span>Laporan penjualan, keuangan, dan stok lengkap</span></div>
            <div class="flex items-start gap-2"><span class="text-emerald-500 font-bold">&check;</span> <span>Program loyalitas: poin, membership tier, diskon</span></div>
            <div class="flex items-start gap-2"><span class="text-emerald-500 font-bold">&check;</span> <span>REST API v1 untuk integrasi mobile &amp; third-party</span></div>
            <div class="flex items-start gap-2"><span class="text-emerald-500 font-bold">&check;</span> <span>Role-based access: owner, manager, admin, kasir</span></div>
            <div class="flex items-start gap-2"><span class="text-emerald-500 font-bold">&check;</span> <span>Audit trail + anti-fraud + approval workflow</span></div>
            <div class="flex items-start gap-2"><span class="text-emerald-500 font-bold">&check;</span> <span>Customer portal untuk self-service pelanggan</span></div>
            <div class="flex items-start gap-2"><span class="text-emerald-500 font-bold">&check;</span> <span>Blog module + PSEO built-in untuk SEO</span></div>
        </div>

        <div class="mt-8 p-6 bg-gray-50 rounded-2xl border border-gray-200">
            <h3 class="font-bold text-gray-900 mb-2">Spesifikasi Teknis</h3>
            <table class="w-full text-sm">
                <tr class="border-b border-gray-200"><td class="py-2 text-gray-500 w-40">Framework</td><td class="py-2 font-semibold">Laravel + FilamentPHP + TailwindCSS</td></tr>
                <tr class="border-b border-gray-200"><td class="py-2 text-gray-500">Database</td><td class="py-2 font-semibold">52 tabel + migration</td></tr>
                <tr class="border-b border-gray-200"><td class="py-2 text-gray-500">Admin Panel</td><td class="py-2 font-semibold">30+ resources + 3 laporan</td></tr>
                <tr class="border-b border-gray-200"><td class="py-2 text-gray-500">API</td><td class="py-2 font-semibold">REST v1 dengan Sanctum</td></tr>
                <tr><td class="py-2 text-gray-500">License</td><td class="py-2 font-semibold">Lifetime + 6 bulan support</td></tr>
            </table>
        </div>
    </article>

    {{-- CTA Bottom --}}
    <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white rounded-3xl p-8 md:p-12 text-center">
        <h2 class="text-2xl md:text-3xl font-extrabold mb-4">Siap Memiliki Source Code {{ $brand }}?</h2>
        <p class="text-blue-200 mb-8 max-w-xl mx-auto">Chat WhatsApp sekarang untuk demo, negosiasi harga, atau konsultasi gratis.</p>
        <a href="https://wa.me/{{ $waNumber }}?text=Halo%20saya%20mau%20beli%20source%20code%20{{ urlencode($brand) }}" target="_blank"
           class="inline-block px-10 py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-bold text-lg shadow-xl shadow-emerald-500/40 transition hover:-translate-y-0.5">
            Beli Source Code — {{ $sourceCodePrice }}
        </a>
    </div>

    {{-- Related Cities --}}
    <div class="mt-10">
        <h3 class="font-bold text-gray-900 mb-4 text-lg">Tersedia di Seluruh Indonesia</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
            @foreach($topCities as $c)
                <a href="/aplikasi-pos-{{ \Illuminate\Support\Str::slug($c['name']) }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline py-1">
                    Aplikasi POS {{ $c['name'] }}
                </a>
            @endforeach
        </div>
    </div>
</main>

<footer class="bg-gray-900 text-gray-400 py-10 mt-12">
    <div class="max-w-4xl mx-auto px-4 text-center text-sm">
        <p>&copy; {{ date('Y') }} {{ $brand }}. Source Code Aplikasi Point of Sale. Beli source code POS, lifetime update.</p>
    </div>
</footer>
</body>
</html>
