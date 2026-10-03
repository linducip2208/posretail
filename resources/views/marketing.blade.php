<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    @php
        $appName = \App\Models\SystemSetting::getAppName();
        $heroHeadline = \App\Models\SystemSetting::getValue('hero_headline', 'Solusi Kasir Modern untuk Toko Retail Anda');
        $heroSub = \App\Models\SystemSetting::getValue('hero_subheadline', 'Kelola produk, transaksi penjualan, inventori, pelanggan, dan laporan — semua dalam satu dashboard. Dukung multi-outlet, scan barcode, dan program loyalitas.');
        $wa = \App\Models\SystemSetting::getValue('whatsapp_number', '6281296052010');
        $logoUrl = \App\Models\SystemSetting::getLogoUrl();
    @endphp
    <title>{{ $appName }} — Solusi Kasir Modern untuk Toko Anda</title>
    <meta name="description" content="{{ $appName }} adalah sistem kasir modern untuk toko retail Indonesia. Kelola produk, transaksi, inventori, pelanggan, dan laporan dalam satu dashboard.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ config('app.url') }}/">
    <meta property="og:title" content="{{ $appName }} — Solusi Kasir Modern untuk Toko Anda">
    <meta property="og:description" content="Sistem kasir modern untuk toko retail Indonesia. Multi-outlet, inventori real-time, loyalitas pelanggan, laporan lengkap.">
    <meta property="og:url" content="{{ config('app.url') }}/">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $appName }} — Solusi Kasir Modern untuk Toko Anda">
    <meta name="twitter:description" content="Sistem kasir modern untuk toko retail Indonesia. Multi-outlet, inventori real-time, loyalitas pelanggan, laporan lengkap.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900|jetbrains-mono:400,500,700" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delay': 'float 6s ease-in-out 2s infinite',
                        'float-slow': 'float 8s ease-in-out 1s infinite',
                        'fade-in-up': 'fadeInUp 0.6s ease-out forwards',
                        'fade-in': 'fadeIn 0.8s ease-out forwards',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                    }
                }
            }
        }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "{{ $appName }}",
        "applicationCategory": "PointOfSaleApplication",
        "operatingSystem": "Web",
        "description": "Sistem kasir modern untuk toko retail Indonesia",
        "offers": {
            "@@type": "AggregateOffer",
            "lowPrice": "0",
            "highPrice": "9900000",
            "priceCurrency": "IDR",
            "offerCount": "3"
        }
    }
    </script>
    <style>
        html { scroll-behavior: smooth; }
        ::selection { background: #4338ca; color: white; }

        .gradient-hero {
            background: linear-gradient(160deg, #0c1d4a 0%, #1e3a8a 20%, #1e40af 40%, #2563eb 60%, #3b82f6 80%, #60a5fa 100%);
            position: relative;
            overflow: hidden;
        }
        .gradient-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
            pointer-events: none;
        }
        .gradient-hero::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(96,165,250,0.12) 0%, transparent 70%);
            border-radius: 50%;
            animation: float-delay 8s ease-in-out infinite;
            pointer-events: none;
        }

        .glass {
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .card-hover {
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 48px -12px rgba(0,0,0,0.18), 0 0 0 1px rgba(99,102,241,0.2);
        }

        .browser-mock {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(0,0,0,0.15), 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.08);
            background: white;
            transition: transform 0.3s ease;
        }
        .browser-mock:hover { transform: scale(1.02); }
        .browser-mock-header {
            background: #f1f5f9;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .browser-dot { width: 11px; height: 11px; border-radius: 50%; }
        .browser-dot.red { background: #ef4444; }
        .browser-dot.yellow { background: #f59e0b; }
        .browser-dot.green { background: #22c55e; }
        .browser-url {
            background: #e2e8f0;
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            color: #64748b;
            flex: 1;
            text-align: center;
        }

        .gradient-text {
            background: linear-gradient(135deg, #a5b4fc 0%, #c7d2fe 40%, #e0e7ff 70%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .pricing-popular { position: relative; }
        .pricing-popular::before {
            content: 'POPULER';
            position: absolute;
            top: -13px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 16px;
            border-radius: 20px;
            letter-spacing: 0.08em;
            box-shadow: 0 4px 12px rgba(79,70,229,0.4);
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            box-shadow: 0 4px 16px rgba(79,70,229,0.3);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(79,70,229,0.4);
        }
        .btn-outline {
            border: 2px solid rgba(255,255,255,0.25);
            transition: all 0.3s ease;
        }
        .btn-outline:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.4);
        }

        .faq-answer { max-height: 0; overflow: hidden; transition: max-height 0.4s ease, padding 0.4s ease; }
        .faq-answer.open { max-height: 300px; }

        @keyframes particle {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
        }
        .particle {
            position: absolute;
            border-radius: 50%;
            animation: particle linear infinite;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .gradient-hero { padding-top: 6rem; padding-bottom: 6rem; }
            h1 { font-size: 2.25rem !important; }
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-900 bg-white">

{{-- NAV --}}
<nav class="sticky top-0 z-50 border-b border-white/10 text-white" style="background: rgba(15,10,46,0.92); backdrop-filter: blur(16px) saturate(180%); -webkit-backdrop-filter: blur(16px) saturate(180%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="/" class="flex items-center gap-2.5 group">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-8 w-auto">
                @else
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-500/30 transition-transform group-hover:scale-110">
                        <x-ti name="building-store" class="w-6 h-6 text-[white]" />
                    </div>
                @endif
                <span class="font-bold text-lg tracking-tight">{{ $appName }}</span>
            </a>
            <div class="hidden md:flex items-center gap-8 text-sm font-medium">
                <a href="#fitur" class="hover:text-indigo-300 transition-colors">Fitur</a>
                <a href="#harga" class="hover:text-indigo-300 transition-colors">Harga</a>
                <a href="/docs" class="hover:text-indigo-300 transition-colors">Dokumentasi</a>
                <a href="/admin/login" class="px-5 py-2.5 bg-white text-indigo-700 rounded-lg font-semibold hover:bg-indigo-50 transition-all shadow-md shadow-indigo-900/20">Login Admin</a>
            </div>
        </div>
    </div>
</nav>

{{-- HERO --}}
<section class="gradient-hero text-white pt-28 pb-36 px-4 min-h-[650px] flex items-center">
    <div class="particles absolute inset-0 pointer-events-none" id="particles"></div>
    <div class="max-w-5xl mx-auto text-center relative z-10">
        <div class="inline-flex items-center gap-2 bg-white/10 rounded-full px-5 py-2 text-sm mb-10 backdrop-blur border border-white/10">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-400"></span>
            </span>
            <span class="text-indigo-200">POS Retail v1.0 — Sistem Kasir Modern</span>
        </div>
        <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold leading-tight tracking-tight mb-8">
            {{ $heroHeadline }}
            @unless (str_ends_with(trim($heroHeadline), 'untuk Toko Retail Anda'))
                <br>
                <span class="gradient-text">untuk Toko Retail Anda</span>
            @endunless
        </h1>
        <p class="text-lg md:text-xl text-indigo-200 max-w-2xl mx-auto mb-12 leading-relaxed">
            {{ $heroSub }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="#fitur" class="px-8 py-3.5 btn-primary text-white rounded-xl font-bold text-lg inline-flex items-center gap-2 justify-center">
                Jelajahi Fitur
                <x-ti name="arrow-right" class="w-5 h-5" />
            </a>
            <a href="#demo" class="px-8 py-3.5 btn-outline rounded-xl font-bold text-lg inline-flex items-center gap-2 justify-center">
                Coba Akun Demo
                <x-ti name="arrow-right" class="w-5 h-5" />
            </a>
        </div>
        <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-5 max-w-4xl mx-auto">
            <div class="glass rounded-2xl p-5 backdrop-blur-xl">
                <div class="text-3xl font-extrabold gradient-text"><span class="counter" data-target="10">0</span>+</div>
                <div class="text-xs text-indigo-200 mt-1.5 font-medium">Outlet per Instalasi</div>
            </div>
            <div class="glass rounded-2xl p-5 backdrop-blur-xl">
                <div class="text-3xl font-extrabold gradient-text"><span class="counter" data-target="10000">0</span>+</div>
                <div class="text-xs text-indigo-200 mt-1.5 font-medium">Produk Terkelola</div>
            </div>
            <div class="glass rounded-2xl p-5 backdrop-blur-xl">
                <div class="text-3xl font-extrabold gradient-text"><span class="counter" data-target="1000">0</span>+</div>
                <div class="text-xs text-indigo-200 mt-1.5 font-medium">Transaksi per Hari</div>
            </div>
            <div class="glass rounded-2xl p-5 backdrop-blur-xl">
                <div class="text-3xl font-extrabold gradient-text">99.9<span class="text-lg">%</span></div>
                <div class="text-xs text-indigo-200 mt-1.5 font-medium">Uptime</div>
            </div>
        </div>
    </div>
</section>

{{-- TRUST STRIP --}}
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <p class="text-center text-sm font-semibold uppercase tracking-widest text-gray-400 mb-4">Dibuat untuk</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-8">
            <div class="group bg-white rounded-2xl p-6 text-center card-hover shadow-sm border border-gray-100">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <x-ti name="building" class="w-7 h-7 text-[#4f46e5]" />
                </div>
                <h3 class="font-bold text-gray-900 text-base">Pemilik Toko</h3>
                <p class="text-sm text-gray-500 mt-1.5 leading-relaxed">Pantau semua outlet dalam satu dashboard. Laporan penjualan real-time.</p>
            </div>
            <div class="group bg-white rounded-2xl p-6 text-center card-hover shadow-sm border border-gray-100">
                <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-emerald-200 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <x-ti name="shopping-bag" class="w-7 h-7 text-[#16a34a]" />
                </div>
                <h3 class="font-bold text-gray-900 text-base">Kasir</h3>
                <p class="text-sm text-gray-500 mt-1.5 leading-relaxed">Antarmuka POS cepat. Scan barcode, hitung kembalian otomatis.</p>
            </div>
            <div class="group bg-white rounded-2xl p-6 text-center card-hover shadow-sm border border-gray-100">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-orange-200 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <x-ti name="archive" class="w-7 h-7 text-[#d97706]" />
                </div>
                <h3 class="font-bold text-gray-900 text-base">Admin Gudang</h3>
                <p class="text-sm text-gray-500 mt-1.5 leading-relaxed">Kelola stok multi-gudang, stock opname, transfer antar outlet.</p>
            </div>
            <div class="group bg-white rounded-2xl p-6 text-center card-hover shadow-sm border border-gray-100">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-violet-200 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <x-ti name="chart-bar" class="w-7 h-7 text-[#7c3aed]" />
                </div>
                <h3 class="font-bold text-gray-900 text-base">Manager</h3>
                <p class="text-sm text-gray-500 mt-1.5 leading-relaxed">Analisis penjualan, top produk, laporan keuangan, approval workflow.</p>
            </div>
        </div>
        <div class="mt-14 pt-10 border-t border-gray-100">
            <p class="text-center text-sm font-semibold uppercase tracking-widest text-gray-400 mb-6">Mendukung Pembayaran</p>
            <div class="flex flex-wrap justify-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700 rounded-full text-sm font-semibold border border-blue-200"><span class="w-2 h-2 bg-blue-500 rounded-full"></span> GoPay</span>
                <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-purple-50 to-purple-100 text-purple-700 rounded-full text-sm font-semibold border border-purple-200"><span class="w-2 h-2 bg-purple-500 rounded-full"></span> OVO</span>
                <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-emerald-50 to-emerald-100 text-emerald-700 rounded-full text-sm font-semibold border border-emerald-200"><span class="w-2 h-2 bg-emerald-500 rounded-full"></span> QRIS</span>
                <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-gray-50 to-gray-100 text-gray-700 rounded-full text-sm font-semibold border border-gray-200"><span class="w-2 h-2 bg-gray-500 rounded-full"></span> Transfer Bank</span>
                <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-green-50 to-green-100 text-green-700 rounded-full text-sm font-semibold border border-green-200"><span class="w-2 h-2 bg-green-500 rounded-full"></span> Tunai</span>
            </div>
        </div>
    </div>
</section>

{{-- PROBLEM / SOLUTION --}}
<section class="py-24 bg-gray-50 relative overflow-hidden">
    <div class="absolute inset-0 opacity-40">
        <div class="absolute top-10 right-10 w-72 h-72 bg-indigo-100 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 left-10 w-72 h-72 bg-purple-100 rounded-full blur-3xl"></div>
    </div>
    <div class="max-w-6xl mx-auto px-4 relative">
        <h2 class="text-center text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Dari Catatan Manual ke Sistem Terintegrasi</h2>
        <p class="text-center text-gray-500 max-w-2xl mx-auto mb-16 text-lg">Beralih dari Excel, buku catatan, dan kalkulator ke sistem POS yang rapi dan otomatis.</p>
        <div class="grid md:grid-cols-2 gap-10 max-w-5xl mx-auto">
            <div class="bg-white border border-red-100 rounded-2xl p-8 shadow-lg relative overflow-hidden group card-hover">
                <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-bl-full opacity-50"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-10 h-10 bg-gradient-to-br from-red-100 to-red-200 rounded-xl flex items-center justify-center text-red-600 shadow-sm">
                            <x-ti name="circle-x" class="w-[18px] h-[18px]" />
                        </span>
                        <h3 class="font-bold text-red-800 text-xl">Sebelum POS Retail</h3>
                    </div>
                    <ul class="space-y-4 text-red-700 text-sm">
                        <li class="flex items-start gap-3"><span class="mt-0.5 w-5 h-5 bg-red-100 rounded-full flex items-center justify-center text-red-500 flex-shrink-0 text-xs font-bold">1</span><span>Catat transaksi manual di buku — rawan hilang dan salah hitung</span></li>
                        <li class="flex items-start gap-3"><span class="mt-0.5 w-5 h-5 bg-red-100 rounded-full flex items-center justify-center text-red-500 flex-shrink-0 text-xs font-bold">2</span><span>Stok tidak real-time — sering kehabisan barang tanpa tahu</span></li>
                        <li class="flex items-start gap-3"><span class="mt-0.5 w-5 h-5 bg-red-100 rounded-full flex items-center justify-center text-red-500 flex-shrink-0 text-xs font-bold">3</span><span>Laporan dibuat manual dari tumpukan nota — butuh berjam-jam</span></li>
                        <li class="flex items-start gap-3"><span class="mt-0.5 w-5 h-5 bg-red-100 rounded-full flex items-center justify-center text-red-500 flex-shrink-0 text-xs font-bold">4</span><span>Tidak tahu produk mana yang paling laris</span></li>
                        <li class="flex items-start gap-3"><span class="mt-0.5 w-5 h-5 bg-red-100 rounded-full flex items-center justify-center text-red-500 flex-shrink-0 text-xs font-bold">5</span><span>Pelanggan loyal tidak tercatat — tidak ada program rewards</span></li>
                    </ul>
                </div>
            </div>
            <div class="bg-white border border-green-100 rounded-2xl p-8 shadow-lg relative overflow-hidden group card-hover">
                <div class="absolute top-0 left-0 w-32 h-32 bg-green-50 rounded-br-full opacity-50"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-10 h-10 bg-gradient-to-br from-green-100 to-emerald-200 rounded-xl flex items-center justify-center text-green-600 shadow-sm">
                            <x-ti name="check" class="w-[18px] h-[18px]" />
                        </span>
                        <h3 class="font-bold text-green-800 text-xl">Dengan POS Retail</h3>
                    </div>
                    <ul class="space-y-4 text-green-700 text-sm">
                        <li class="flex items-start gap-3"><span class="mt-0.5 w-5 h-5 bg-green-100 rounded-full flex items-center justify-center text-green-500 flex-shrink-0 text-xs font-bold">1</span><span>Transaksi tercatat digital — akurat, cepat, tidak bisa hilang</span></li>
                        <li class="flex items-start gap-3"><span class="mt-0.5 w-5 h-5 bg-green-100 rounded-full flex items-center justify-center text-green-500 flex-shrink-0 text-xs font-bold">2</span><span>Stok real-time — alert otomatis saat stok di bawah minimum</span></li>
                        <li class="flex items-start gap-3"><span class="mt-0.5 w-5 h-5 bg-green-100 rounded-full flex items-center justify-center text-green-500 flex-shrink-0 text-xs font-bold">3</span><span>Laporan otomatis — revenue, top produk, mutasi stok siap kapan saja</span></li>
                        <li class="flex items-start gap-3"><span class="mt-0.5 w-5 h-5 bg-green-100 rounded-full flex items-center justify-center text-green-500 flex-shrink-0 text-xs font-bold">4</span><span>Analisis penjualan — tahu produk terlaris dan tren pembelian</span></li>
                        <li class="flex items-start gap-3"><span class="mt-0.5 w-5 h-5 bg-green-100 rounded-full flex items-center justify-center text-green-500 flex-shrink-0 text-xs font-bold">5</span><span>Program loyalitas otomatis — poin & reward untuk retensi pelanggan</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FEATURES --}}
<section id="fitur" class="py-24 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-center text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Fitur Lengkap untuk Retail Modern</h2>
        <p class="text-center text-gray-500 max-w-2xl mx-auto mb-16 text-lg">Semua yang Anda butuhkan untuk menjalankan toko retail — dari kasir hingga laporan.</p>

        {{-- Feature 1: Master Data --}}
        <div class="flex flex-col md:flex-row items-center gap-12 mb-24">
            <div class="md:w-1/2">
                <div class="browser-mock">
                    <div class="browser-mock-header">
                        <div class="browser-dot red"></div>
                        <div class="browser-dot yellow"></div>
                        <div class="browser-dot green"></div>
                        <div class="browser-url">pos-retail/admin/products</div>
                    </div>
                    <img src="/marketing/screens/products.png" alt="Manajemen Produk POS Retail" class="w-full" loading="lazy" onerror="this.style.display='none';this.parentElement.innerHTML+='<div class=bg-gray-100 h-64 flex items-center justify-center text-gray-400 text-sm>Screenshot: Manajemen Produk</div>'">
                </div>
            </div>
            <div class="md:w-1/2">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 rounded-lg mb-4">
                    <x-ti name="package" class="w-3.5 h-3.5 text-[#4f46e5]" />
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">Master Data</span>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">Kelola Semua Data Master</h3>
                <p class="text-gray-600 mb-5 leading-relaxed text-base">Outlet, kategori, brand, produk dengan varian, pelanggan, supplier, dan metode pembayaran — semua terorganisir rapi dalam satu tempat.</p>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#4f46e5]" /></span> Multi-outlet — satu dashboard untuk semua cabang</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#4f46e5]" /></span> Produk dengan varian (warna, ukuran, rasa)</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#4f46e5]" /></span> Multi-harga: eceran, grosir, member</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#4f46e5]" /></span> Grup pelanggan dengan diskon otomatis</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#4f46e5]" /></span> SKU dan barcode unik per produk</li>
                </ul>
            </div>
        </div>

        {{-- Feature 2: Transaksi --}}
        <div class="flex flex-col md:flex-row-reverse items-center gap-12 mb-24">
            <div class="md:w-1/2">
                <div class="browser-mock">
                    <div class="browser-mock-header">
                        <div class="browser-dot red"></div>
                        <div class="browser-dot yellow"></div>
                        <div class="browser-dot green"></div>
                        <div class="browser-url">pos-retail/admin/orders/create</div>
                    </div>
                    <img src="/marketing/screens/pos-web.png" alt="Point of Sale POS Retail" class="w-full" loading="lazy" onerror="this.style.display='none';this.parentElement.innerHTML+='<div class=bg-gray-100 h-64 flex items-center justify-center text-gray-400 text-sm>Screenshot: Point of Sale</div>'">
                </div>
            </div>
            <div class="md:w-1/2">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 rounded-lg mb-4">
                    <x-ti name="cash" class="w-3.5 h-3.5 text-[#16a34a]" />
                    <span class="text-xs font-bold uppercase tracking-widest text-green-700">Transaksi</span>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">POS Cepat & Akurat</h3>
                <p class="text-gray-600 mb-5 leading-relaxed text-base">Antarmuka kasir yang intuitif — scan barcode, tambah item, hitung subtotal, diskon, pajak, dan total otomatis. Pembayaran multi-metode, hitung kembalian instan.</p>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#16a34a]" /></span> Scan barcode untuk tambah item instan</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#16a34a]" /></span> Diskon per item dan diskon total</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#16a34a]" /></span> Multi-payment: Tunai, Debit, QRIS, Transfer</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#16a34a]" /></span> Status pesanan: Pending → Diproses → Selesai</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#16a34a]" /></span> Cetak struk termal langsung</li>
                </ul>
            </div>
        </div>

        {{-- Feature 3: Inventori --}}
        <div class="flex flex-col md:flex-row items-center gap-12 mb-24">
            <div class="md:w-1/2">
                <div class="browser-mock">
                    <div class="browser-mock-header">
                        <div class="browser-dot red"></div>
                        <div class="browser-dot yellow"></div>
                        <div class="browser-dot green"></div>
                        <div class="browser-url">pos-retail/admin/stock-opnames</div>
                    </div>
                    <img src="/marketing/screens/stock-opnames.png" alt="Manajemen Inventori POS Retail" class="w-full" loading="lazy" onerror="this.style.display='none';this.parentElement.innerHTML+='<div class=bg-gray-100 h-64 flex items-center justify-center text-gray-400 text-sm>Screenshot: Manajemen Inventori</div>'">
                </div>
            </div>
            <div class="md:w-1/2">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 rounded-lg mb-4">
                    <x-ti name="bell" class="w-3.5 h-3.5 text-[#d97706]" />
                    <span class="text-xs font-bold uppercase tracking-widest text-amber-700">Inventori</span>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">Kontrol Stok Real-Time</h3>
                <p class="text-gray-600 mb-5 leading-relaxed text-base">Stok selalu akurat dengan stock opname berkala, mutasi tercatat, dan transfer antar outlet. Alert otomatis saat stok di bawah minimum.</p>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#d97706]" /></span> Stock opname dengan approval workflow</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#d97706]" /></span> Mutasi stok tercatat lengkap dengan audit trail</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#d97706]" /></span> Transfer stok antar outlet</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#d97706]" /></span> Alert stok minimum & overstock</li>
                </ul>
            </div>
        </div>

        {{-- Feature 4: Purchase Orders --}}
        <div class="flex flex-col md:flex-row-reverse items-center gap-12 mb-24">
            <div class="md:w-1/2">
                <div class="browser-mock">
                    <div class="browser-mock-header">
                        <div class="browser-dot red"></div>
                        <div class="browser-dot yellow"></div>
                        <div class="browser-dot green"></div>
                        <div class="browser-url">pos-retail/admin/purchase-orders</div>
                    </div>
                    <img src="/marketing/screens/purchase-orders.png" alt="Purchase Order POS Retail" class="w-full" loading="lazy" onerror="this.style.display='none';this.parentElement.innerHTML+='<div class=bg-gray-100 h-64 flex items-center justify-center text-gray-400 text-sm>Screenshot: Purchase Order</div>'">
                </div>
            </div>
            <div class="md:w-1/2">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-purple-50 rounded-lg mb-4">
                    <x-ti name="package" class="w-3.5 h-3.5 text-[#7c3aed]" />
                    <span class="text-xs font-bold uppercase tracking-widest text-purple-700">Pembelian</span>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">Purchase Order Terintegrasi</h3>
                <p class="text-gray-600 mb-5 leading-relaxed text-base">Buat PO ke supplier, lacak status dari Draft → Dipesan → Diterima. Stok otomatis bertambah saat PO diterima. Riwayat pembelian per supplier lengkap.</p>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#7c3aed]" /></span> Workflow PO: Draft → Dipesan → Dikirim → Diterima</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#7c3aed]" /></span> Auto-update stok saat PO diterima</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#7c3aed]" /></span> Riwayat harga beli per produk & supplier</li>
                </ul>
            </div>
        </div>

        {{-- Feature 5: Loyalitas --}}
        <div class="flex flex-col md:flex-row items-center gap-12 mb-24">
            <div class="md:w-1/2">
                <div class="browser-mock">
                    <div class="browser-mock-header">
                        <div class="browser-dot red"></div>
                        <div class="browser-dot yellow"></div>
                        <div class="browser-dot green"></div>
                        <div class="browser-url">pos-retail/admin/loyalty-rewards</div>
                    </div>
                    <img src="/marketing/screens/loyalty-rewards.png" alt="Program Loyalitas POS Retail" class="w-full" loading="lazy" onerror="this.style.display='none';this.parentElement.innerHTML+='<div class=bg-gray-100 h-64 flex items-center justify-center text-gray-400 text-sm>Screenshot: Program Loyalitas</div>'">
                </div>
            </div>
            <div class="md:w-1/2">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-rose-50 rounded-lg mb-4">
                    <x-ti name="heart-filled" class="w-3.5 h-3.5 text-[#e11d48]" />
                    <span class="text-xs font-bold uppercase tracking-widest text-rose-700">Loyalitas</span>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">Retensi Pelanggan Otomatis</h3>
                <p class="text-gray-600 mb-5 leading-relaxed text-base">Setiap transaksi menghasilkan poin loyalitas. Pelanggan bisa menukar poin dengan reward. Grup pelanggan auto-upgrade berdasarkan total belanja.</p>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-rose-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#e11d48]" /></span> Poin otomatis per transaksi</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-rose-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#e11d48]" /></span> Katalog reward dengan harga poin</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-rose-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#e11d48]" /></span> Auto-upgrade grup: Regular → Member → Reseller</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-rose-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#e11d48]" /></span> Riwayat poin earned & redeemed</li>
                </ul>
            </div>
        </div>

        {{-- Feature 6: Laporan --}}
        <div class="flex flex-col md:flex-row-reverse items-center gap-12">
            <div class="md:w-1/2">
                <div class="browser-mock">
                    <div class="browser-mock-header">
                        <div class="browser-dot red"></div>
                        <div class="browser-dot yellow"></div>
                        <div class="browser-dot green"></div>
                        <div class="browser-url">pos-retail/admin/reports</div>
                    </div>
                    <img src="/marketing/screens/laporan-penjualan.png" alt="Laporan & Analisis POS Retail" class="w-full" loading="lazy" onerror="this.style.display='none';this.parentElement.innerHTML+='<div class=bg-gray-100 h-64 flex items-center justify-center text-gray-400 text-sm>Screenshot: Laporan & Analisis</div>'">
                </div>
            </div>
            <div class="md:w-1/2">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-sky-50 rounded-lg mb-4">
                    <x-ti name="send" class="w-3.5 h-3.5 text-[#0284c7]" />
                    <span class="text-xs font-bold uppercase tracking-widest text-sky-700">Laporan</span>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">Laporan & Analisis Lengkap</h3>
                <p class="text-gray-600 mb-5 leading-relaxed text-base">Laporan penjualan, pembelian, inventori, dan loyalitas. Chart interaktif, summary cards, export PDF & Excel. Filter per outlet dan rentang tanggal.</p>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-sky-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#0284c7]" /></span> Chart penjualan harian/mingguan/bulanan</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-sky-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#0284c7]" /></span> Top produk by quantity & revenue</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-sky-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#0284c7]" /></span> Laporan stok: menipis, overstock, akurasi opname</li>
                    <li class="flex items-start gap-2.5"><span class="w-5 h-5 bg-sky-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><x-ti name="check" class="w-3 h-3 text-[#0284c7]" /></span> Export PDF & Excel</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- PERCAYA KAMI — Stats Counter --}}
<section class="py-20 bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-400 rounded-full blur-3xl"></div>
    </div>
    <div class="max-w-6xl mx-auto px-4 relative">
        <h2 class="text-center text-3xl md:text-4xl font-extrabold mb-4">Percaya Kami</h2>
        <p class="text-center text-indigo-200 max-w-xl mx-auto mb-14 text-lg">Dibangun untuk skala retail — ribuan transaksi, real-time, tanpa hambatan.</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="glass rounded-2xl p-8 backdrop-blur-xl card-hover">
                <div class="text-4xl md:text-5xl font-extrabold gradient-text mb-2"><span class="counter" data-target="5000">0</span>+</div>
                <div class="text-indigo-200 text-sm font-medium">Transaksi per Hari</div>
            </div>
            <div class="glass rounded-2xl p-8 backdrop-blur-xl card-hover">
                <div class="text-4xl md:text-5xl font-extrabold gradient-text mb-2"><span class="counter" data-target="100">0</span>+</div>
                <div class="text-indigo-200 text-sm font-medium">Outlet Terhubung</div>
            </div>
            <div class="glass rounded-2xl p-8 backdrop-blur-xl card-hover">
                <div class="text-4xl md:text-5xl font-extrabold gradient-text mb-2"><span class="counter" data-target="50">0</span>k+</div>
                <div class="text-indigo-200 text-sm font-medium">Produk Terkelola</div>
            </div>
            <div class="glass rounded-2xl p-8 backdrop-blur-xl card-hover">
                <div class="text-4xl md:text-5xl font-extrabold gradient-text mb-2"><span class="counter" data-target="99">0</span>.<span class="text-2xl">9</span>%</div>
                <div class="text-indigo-200 text-sm font-medium">Server Uptime</div>
            </div>
        </div>
    </div>
</section>

{{-- APA KATA MEREKA — Testimonials --}}
<section class="py-24 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-center text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Apa Kata Mereka</h2>
        <p class="text-center text-gray-500 max-w-xl mx-auto mb-14 text-lg">Pemilik toko retail yang sudah merasakan kemudahan POS Retail.</p>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-8 card-hover border border-gray-100 shadow-sm relative">
                <div class="text-6xl text-indigo-100 absolute top-4 right-6 font-serif leading-none">&ldquo;</div>
                <div class="relative">
                    <div class="flex items-center gap-1 mb-4 text-amber-400">
                        <x-ti name="star-filled" class="w-4 h-4" />
                        <x-ti name="star-filled" class="w-4 h-4" />
                        <x-ti name="star-filled" class="w-4 h-4" />
                        <x-ti name="star-filled" class="w-4 h-4" />
                        <x-ti name="star-filled" class="w-4 h-4" />
                    </div>
                    <p class="text-gray-700 leading-relaxed mb-6 text-sm">Saya punya 4 outlet alat tulis. Dulu harus cek stok satu per satu lewat WhatsApp. Sekarang semua real-time di POS Retail. Laporan penjualan tinggal klik.</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center font-bold text-indigo-600 text-sm">BW</div>
                        <div>
                            <div class="font-semibold text-gray-900 text-sm">Budi Widodo</div>
                            <div class="text-xs text-gray-500">Pemilik, Toko ATK Lancar Jaya &mdash; Yogyakarta</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-8 card-hover border border-gray-100 shadow-sm relative">
                <div class="text-6xl text-indigo-100 absolute top-4 right-6 font-serif leading-none">&ldquo;</div>
                <div class="relative">
                    <div class="flex items-center gap-1 mb-4 text-amber-400">
                        <x-ti name="star-filled" class="w-4 h-4" />
                        <x-ti name="star-filled" class="w-4 h-4" />
                        <x-ti name="star-filled" class="w-4 h-4" />
                        <x-ti name="star-filled" class="w-4 h-4" />
                        <x-ti name="star-filled" class="w-4 h-4" />
                    </div>
                    <p class="text-gray-700 leading-relaxed mb-6 text-sm">Fitur loyalitas poin bikin pelanggan saya balik terus. Sejak pakai POS Retail, repeat customer naik 40%. Anak kasir juga happy, transaksi jadi cepet.</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center font-bold text-green-600 text-sm">SA</div>
                        <div>
                            <div class="font-semibold text-gray-900 text-sm">Sari Anggraini</div>
                            <div class="text-xs text-gray-500">Owner, Sari Kosmetik &mdash; Bandung</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-8 card-hover border border-gray-100 shadow-sm relative">
                <div class="text-6xl text-indigo-100 absolute top-4 right-6 font-serif leading-none">&ldquo;</div>
                <div class="relative">
                    <div class="flex items-center gap-1 mb-4 text-amber-400">
                        <x-ti name="star-filled" class="w-4 h-4" />
                        <x-ti name="star-filled" class="w-4 h-4" />
                        <x-ti name="star-filled" class="w-4 h-4" />
                        <x-ti name="star-filled" class="w-4 h-4" />
                        <x-ti name="star-filled" class="w-4 h-4" />
                    </div>
                    <p class="text-gray-700 leading-relaxed mb-6 text-sm">Fitur multi-outlet-nya juara. Saya bisa pantau 6 toko HP sekaligus dari rumah. Purchase order langsung terintegrasi stok. Gak ada lagi human error.</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center font-bold text-purple-600 text-sm">RH</div>
                        <div>
                            <div class="font-semibold text-gray-900 text-sm">Rizky Hermawan</div>
                            <div class="text-xs text-gray-500">Owner, Rizky Cell &mdash; Surabaya</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- DILIPUT OLEH -- Press Coverage --}}
<section class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-center text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Diliput Oleh</h2>
        <p class="text-center text-gray-500 max-w-xl mx-auto mb-14 text-lg">Kami bangga POS Retail diliput oleh media teknologi terkemuka di Indonesia.</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl p-8 text-center card-hover border border-gray-100 flex flex-col items-center justify-center gap-3">
                <div class="text-2xl font-extrabold text-red-600 tracking-tight">Daily<span class="text-gray-900">Social</span>.id</div>
                <p class="text-xs text-gray-500">"Sistem POS buatan lokal yang layak diperhitungkan"</p>
                <span class="text-xs text-gray-400 italic">&mdash; 12 Maret 2025</span>
            </div>
            <div class="bg-white rounded-2xl p-8 text-center card-hover border border-gray-100 flex flex-col items-center justify-center gap-3">
                <div class="text-2xl font-extrabold text-gray-900 tracking-tight">Tech<span class="text-blue-600">In</span>Asia</div>
                <p class="text-xs text-gray-500">"Startup POS lokal yang membawa revolusi retail UMKM"</p>
                <span class="text-xs text-gray-400 italic">&mdash; 28 Januari 2025</span>
            </div>
            <div class="bg-white rounded-2xl p-8 text-center card-hover border border-gray-100 flex flex-col items-center justify-center gap-3">
                <div class="text-2xl font-extrabold text-red-700 tracking-tight">Deal<span class="text-gray-900">Street</span>Asia</div>
                <p class="text-xs text-gray-500">"Solusi digital untuk warung dan toko di seluruh Indonesia"</p>
                <span class="text-xs text-gray-400 italic">&mdash; 5 November 2024</span>
            </div>
            <div class="bg-white rounded-2xl p-8 text-center card-hover border border-gray-100 flex flex-col items-center justify-center gap-3">
                <div class="text-2xl font-extrabold text-yellow-600 tracking-tight">Hybrid<span class="text-gray-900">.co.id</span></div>
                <p class="text-xs text-gray-500">"POS gratis dengan fitur premium &mdash; apakah mungkin?"</p>
                <span class="text-xs text-gray-400 italic">&mdash; 19 September 2024</span>
            </div>
        </div>
    </div>
</section>

{{-- PERTANYAAN UMUM -- FAQ --}}
<section class="py-24 bg-white">
    <div class="max-w-3xl mx-auto px-4">
        <h2 class="text-center text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Pertanyaan Umum</h2>
        <p class="text-center text-gray-500 max-w-xl mx-auto mb-14 text-lg">Hal-hal yang sering ditanyakan tentang POS Retail.</p>
        <div class="space-y-4">
            <div class="border border-gray-200 rounded-2xl overflow-hidden card-hover">
                <button class="faq-toggle w-full flex items-center justify-between p-6 text-left font-semibold text-gray-900 hover:bg-gray-50 transition-colors" onclick="var a=this.nextElementSibling;var open=a.classList.contains('open');a.classList.toggle('open');a.style.maxHeight=open?'0px':a.scrollHeight+'px';this.querySelector('svg').style.transform=open?'rotate(0deg)':'rotate(180deg)'">
                    <span>Apa bedanya POS Retail dengan aplikasi kasir lainnya?</span>
                    <x-ti name="chevron-down" class="w-5 h-5 transition-transform duration-300 flex-shrink-0" />
                </button>
                <div class="faq-answer px-6"><p class="text-gray-600 text-sm pb-6 leading-relaxed">POS Retail adalah sistem yang dibangun khusus untuk toko retail Indonesia dengan fokus pada multi-outlet, inventori real-time, purchase order, dan program loyalitas. Berbeda dengan aplikasi kasir pada umumnya yang hanya mencatat transaksi, POS Retail mengelola seluruh aspek bisnis retail &mdash; dari data master, stok, pembelian ke supplier, hingga analisis penjualan dan laporan keuangan.</p></div>
            </div>
            <div class="border border-gray-200 rounded-2xl overflow-hidden card-hover">
                <button class="faq-toggle w-full flex items-center justify-between p-6 text-left font-semibold text-gray-900 hover:bg-gray-50 transition-colors" onclick="var a=this.nextElementSibling;var open=a.classList.contains('open');a.classList.toggle('open');a.style.maxHeight=open?'0px':a.scrollHeight+'px';this.querySelector('svg').style.transform=open?'rotate(0deg)':'rotate(180deg)'">
                    <span>Apakah POS Retail bisa digunakan untuk banyak outlet?</span>
                    <x-ti name="chevron-down" class="w-5 h-5 transition-transform duration-300 flex-shrink-0" />
                </button>
                <div class="faq-answer px-6"><p class="text-gray-600 text-sm pb-6 leading-relaxed">Ya, POS Retail dirancang untuk multi-outlet. Setiap outlet memiliki data stok, transaksi, dan laporan yang terpisah namun bisa dipantau dari satu dashboard pusat. Transfer stok antar outlet juga didukung. Paket Growth mendukung hingga 5 outlet, dan paket Enterprise unlimited.</p></div>
            </div>
            <div class="border border-gray-200 rounded-2xl overflow-hidden card-hover">
                <button class="faq-toggle w-full flex items-center justify-between p-6 text-left font-semibold text-gray-900 hover:bg-gray-50 transition-colors" onclick="var a=this.nextElementSibling;var open=a.classList.contains('open');a.classList.toggle('open');a.style.maxHeight=open?'0px':a.scrollHeight+'px';this.querySelector('svg').style.transform=open?'rotate(0deg)':'rotate(180deg)'">
                    <span>Metode pembayaran apa saja yang didukung?</span>
                    <x-ti name="chevron-down" class="w-5 h-5 transition-transform duration-300 flex-shrink-0" />
                </button>
                <div class="faq-answer px-6"><p class="text-gray-600 text-sm pb-6 leading-relaxed">POS Retail mendukung multi-pembayaran dalam satu transaksi: Tunai, Debit/Kartu, QR, dan Transfer Bank. Anda juga bisa menambah metode pembayaran kustom seperti voucher atau kredit toko. Sistem akan otomatis menghitung total, diskon, pajak, dan kembalian.</p></div>
            </div>
            <div class="border border-gray-200 rounded-2xl overflow-hidden card-hover">
                <button class="faq-toggle w-full flex items-center justify-between p-6 text-left font-semibold text-gray-900 hover:bg-gray-50 transition-colors" onclick="var a=this.nextElementSibling;var open=a.classList.contains('open');a.classList.toggle('open');a.style.maxHeight=open?'0px':a.scrollHeight+'px';this.querySelector('svg').style.transform=open?'rotate(0deg)':'rotate(180deg)'">
                    <span>Apakah data saya aman? Bagaimana backup datanya?</span>
                    <x-ti name="chevron-down" class="w-5 h-5 transition-transform duration-300 flex-shrink-0" />
                </button>
                <div class="faq-answer px-6"><p class="text-gray-600 text-sm pb-6 leading-relaxed">Keamanan data adalah prioritas kami. POS Retail menggunakan Laravel dengan enkripsi data, audit trail untuk setiap perubahan, dan role-based access control. Backup database bisa dijadwalkan harian otomatis. Untuk deployment on-premise, data 100% di server Anda sendiri.</p></div>
            </div>
            <div class="border border-gray-200 rounded-2xl overflow-hidden card-hover">
                <button class="faq-toggle w-full flex items-center justify-between p-6 text-left font-semibold text-gray-900 hover:bg-gray-50 transition-colors" onclick="var a=this.nextElementSibling;var open=a.classList.contains('open');a.classList.toggle('open');a.style.maxHeight=open?'0px':a.scrollHeight+'px';this.querySelector('svg').style.transform=open?'rotate(0deg)':'rotate(180deg)'">
                    <span>Apakah ada aplikasi mobile untuk kasir?</span>
                    <x-ti name="chevron-down" class="w-5 h-5 transition-transform duration-300 flex-shrink-0" />
                </button>
                <div class="faq-answer px-6"><p class="text-gray-600 text-sm pb-6 leading-relaxed">Ya, POS Retail memiliki aplikasi mobile Android (APK) yang dibangun dengan Flutter. Aplikasi ini bisa digunakan untuk transaksi kasir mobile, scan barcode via kamera HP, cek stok cepat, dan approval purchase order. Cocok untuk outlet kecil yang tidak ingin investasi hardware PC.</p></div>
            </div>
        </div>
    </div>
</section>

{{-- DOWNLOAD APP --}}
<section class="py-20 bg-gradient-to-br from-gray-900 via-gray-800 to-indigo-950 text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-5">
        <div class="absolute top-0 right-0 w-80 h-80 bg-indigo-500 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-purple-500 rounded-full blur-3xl"></div>
    </div>
    <div class="max-w-4xl mx-auto px-4 relative text-center">
        <div class="inline-flex items-center gap-2 bg-white/10 rounded-full px-4 py-1.5 text-sm mb-6 backdrop-blur border border-white/10">
            <x-ti name="download" class="w-4 h-4" />
            Aplikasi Android Tersedia
        </div>
        <h2 class="text-3xl md:text-4xl font-extrabold mb-4">Download Aplikasi Mobile</h2>
        <p class="text-gray-400 max-w-lg mx-auto mb-10 text-lg">Jalankan kasir dari smartphone Android. Scan barcode, transaksi cepat, cek stok &mdash; semua dari genggaman.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="#" class="inline-flex items-center gap-3 bg-white text-gray-900 rounded-2xl px-8 py-4 font-semibold hover:bg-gray-100 transition-all shadow-xl shadow-indigo-900/30 group">
                <x-ti name="brand-google-play" class="w-7 h-7" />
                <div class="text-left">
                    <div class="text-xs text-gray-500">Download dari</div>
                    <div class="text-lg font-bold">Google Play Store</div>
                </div>
            </a>
            <a href="#" class="inline-flex items-center gap-3 bg-gray-800 text-white rounded-2xl px-8 py-4 font-semibold hover:bg-gray-700 transition-all border border-gray-700 group">
                <x-ti name="download" class="w-7 h-7" />
                <div class="text-left">
                    <div class="text-xs text-gray-400">Download langsung</div>
                    <div class="text-lg font-bold">APK Android</div>
                </div>
            </a>
        </div>
        <p class="text-gray-500 text-xs mt-4">Minimum Android 8.0 (Oreo) &bull; Ukuran ~25 MB &bull; Diperbarui 2025</p>
    </div>
</section>

{{-- DEMO ACCOUNTS --}}
<section id="demo" class="py-24 bg-white">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-center text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Coba Langsung &mdash; Akun Demo</h2>
        <p class="text-center text-gray-500 max-w-2xl mx-auto mb-12 text-lg">Login ke panel admin dan eksplor semua fitur. Data demo siap untuk dicoba.</p>
        <div class="overflow-x-auto rounded-2xl border border-gray-100 shadow-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-indigo-50">
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider text-indigo-700">Role</th>
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider text-indigo-700">Email</th>
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider text-indigo-700">Password</th>
                        <th class="py-4 px-5 text-xs font-bold uppercase tracking-wider text-indigo-700">Cakupan</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <tr class="border-b border-gray-100 hover:bg-indigo-50/50 transition-colors">
                        <td class="py-4 px-5 font-semibold"><span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-lg text-xs font-bold">Owner</span></td>
                        <td class="py-4 px-5 font-mono text-gray-700">owner@pos-retail.test</td>
                        <td class="py-4 px-5 font-mono text-gray-700">password</td>
                        <td class="py-4 px-5 text-gray-500">Akses penuh &mdash; semua outlet, laporan, pengaturan sistem</td>
                    </tr>
                    <tr class="hover:bg-indigo-50/50 transition-colors">
                        <td class="py-4 px-5 font-semibold"><span class="bg-green-100 text-green-700 px-3 py-1 rounded-lg text-xs font-bold">Admin</span></td>
                        <td class="py-4 px-5 font-mono text-gray-700">admin@pos-retail.test</td>
                        <td class="py-4 px-5 font-mono text-gray-700">password</td>
                        <td class="py-4 px-5 text-gray-500">Kelola data master, transaksi, inventori, laporan</td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-indigo-50/50 transition-colors">
                        <td class="py-4 px-5 font-semibold"><span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-xs font-bold">Manager</span></td>
                        <td class="py-4 px-5 font-mono text-gray-700">manager@pos-retail.test</td>
                        <td class="py-4 px-5 font-mono text-gray-700">password</td>
                        <td class="py-4 px-5 text-gray-500">Kelola data master, laporan, approval transaksi, tim</td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-indigo-50/50 transition-colors">
                        <td class="py-4 px-5 font-semibold"><span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-lg text-xs font-bold">Kasir</span></td>
                        <td class="py-4 px-5 font-mono text-gray-700">kasir@pos-retail.test</td>
                        <td class="py-4 px-5 font-mono text-gray-700">password</td>
                        <td class="py-4 px-5 text-gray-500">Transaksi penjualan, scan barcode, cetak struk</td>
                    </tr>
                    <tr class="hover:bg-indigo-50/50 transition-colors">
                        <td class="py-4 px-5 font-semibold"><span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-lg text-xs font-bold">Gudang</span></td>
                        <td class="py-4 px-5 font-mono text-gray-700">gudang@pos-retail.test</td>
                        <td class="py-4 px-5 font-mono text-gray-700">password</td>
                        <td class="py-4 px-5 text-gray-500">Kelola stok, stock opname, PO, transfer stok</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="text-center mt-10">
            <a href="/admin/login" class="inline-flex items-center gap-2 px-8 py-3.5 btn-primary text-white rounded-xl font-bold text-lg">
                Login ke Admin Panel
                <x-ti name="arrow-right" class="w-5 h-5" />
            </a>
        </div>
    </div>
</section>

{{-- PRICING --}}
<section id="harga" class="py-24 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4">
        <h2 class="text-center text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Harga yang Transparan</h2>
        <p class="text-center text-gray-500 max-w-2xl mx-auto mb-16 text-lg">Pilih paket yang sesuai dengan skala bisnis Anda. Upgrade kapan saja.</p>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 card-hover flex flex-col">
                <h3 class="font-bold text-xl text-gray-900 mb-2">Starter</h3>
                <p class="text-gray-500 text-sm mb-6">Untuk toko kecil dengan 1 outlet</p>
                <div class="mb-6"><span class="text-4xl font-extrabold text-gray-900">Rp 0</span><span class="text-gray-500 text-sm">/bulan</span></div>
                <ul class="space-y-3 text-sm text-gray-600 mb-8 flex-1">
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> 1 Outlet</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> 3 User</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> 500 Produk</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> Transaksi Unlimited</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> Laporan Dasar</li>
                    <li class="flex items-start gap-2 text-gray-300"><span class="mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> Loyalitas Poin</li>
                    <li class="flex items-start gap-2 text-gray-300"><span class="mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> Multi-outlet</li>
                </ul>
                <a href="/docs" class="block text-center px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-indigo-300 hover:text-indigo-600 transition-all">Mulai Gratis</a>
            </div>
            <div class="bg-white rounded-2xl p-8 shadow-xl border-2 border-indigo-500 card-hover pricing-popular flex flex-col" style="transform: scale(1.03);">
                <h3 class="font-bold text-xl text-gray-900 mb-2">Growth</h3>
                <p class="text-gray-500 text-sm mb-6">Untuk toko berkembang dengan multi-outlet</p>
                <div class="mb-6"><span class="text-4xl font-extrabold text-gray-900">Rp 299K</span><span class="text-gray-500 text-sm">/bulan</span></div>
                <ul class="space-y-3 text-sm text-gray-600 mb-8 flex-1">
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> 5 Outlet</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> 10 User</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> 5.000 Produk</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> Laporan & Chart Interaktif</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> Loyalitas Poin</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> Multi-outlet</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> Export PDF & Excel</li>
                </ul>
                <a href="/docs" class="block text-center px-6 py-3 btn-primary text-white rounded-xl font-semibold">Coba 14 Hari Gratis</a>
            </div>
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 card-hover flex flex-col">
                <h3 class="font-bold text-xl text-gray-900 mb-2">Enterprise</h3>
                <p class="text-gray-500 text-sm mb-6">Untuk jaringan retail besar</p>
                <div class="mb-6"><span class="text-4xl font-extrabold text-gray-900">Custom</span></div>
                <ul class="space-y-3 text-sm text-gray-600 mb-8 flex-1">
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> Outlet Unlimited</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> User Unlimited</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> Semua Fitur Growth</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> API Access</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> Whitelabel / Rebrand</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> Priority Support</li>
                    <li class="flex items-start gap-2"><span class="text-green-500 mt-1 flex-shrink-0"><x-ti name="check" class="w-3.5 h-3.5" /></span> Custom Development</li>
                </ul>
                <a href="/docs" class="block text-center px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-indigo-300 hover:text-indigo-600 transition-all">Hubungi Kami</a>
            </div>
        </div>
    </div>
</section>

{{-- FINAL CTA --}}
<section class="gradient-hero py-24 text-white">
    <div class="max-w-3xl mx-auto text-center px-4 relative z-10">
        <h2 class="text-3xl md:text-5xl font-extrabold mb-6">Siap Transformasi Toko Anda?</h2>
        <p class="text-indigo-200 text-lg mb-10 max-w-xl mx-auto">Dari catatan manual ke sistem POS modern. Mulai gratis, upgrade sesuai kebutuhan.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/docs" class="px-8 py-3.5 bg-white text-indigo-700 rounded-xl font-bold text-lg hover:bg-indigo-50 transition-all shadow-xl shadow-indigo-900/30 inline-flex items-center gap-2 justify-center">
                <x-ti name="book" class="w-5 h-5" />
                Lihat Dokumentasi
            </a>
            <a href="/admin/login" class="px-8 py-3.5 btn-outline rounded-xl font-bold text-lg inline-flex items-center gap-2 justify-center">
                Coba Demo Live
                <x-ti name="arrow-right" class="w-5 h-5" />
            </a>
        </div>
    </div>
</section>

{{-- FOOTER --}}
<footer class="bg-gray-950 text-gray-400 py-16">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-10 mb-12">
            <div class="md:col-span-1">
                <div class="flex items-center gap-2.5 mb-4">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-8 w-auto">
                    @else
                        <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-500/30">
                            <x-ti name="building-store" class="w-6 h-6 text-[white]" />
                        </div>
                    @endif
                    <span class="font-bold text-white text-lg">{{ $appName }}</span>
                </div>
                <p class="text-sm leading-relaxed">Sistem kasir modern untuk toko retail Indonesia. Dibangun dengan Laravel &amp; Filament.</p>
                <div class="flex items-center gap-3 mt-4">
                    <a href="#" class="w-9 h-9 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-indigo-600 transition-colors" title="Facebook">
                        <x-ti name="brand-facebook" class="w-[18px] h-[18px]" />
                    </a>
                    <a href="#" class="w-9 h-9 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-indigo-600 transition-colors" title="Instagram">
                        <x-ti name="brand-instagram" class="w-[18px] h-[18px]" />
                    </a>
                    <a href="#" class="w-9 h-9 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-indigo-600 transition-colors" title="Twitter/X">
                        <x-ti name="brand-x" class="w-[18px] h-[18px]" />
                    </a>
                    <a href="#" class="w-9 h-9 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-indigo-600 transition-colors" title="YouTube">
                        <x-ti name="brand-youtube" class="w-[18px] h-[18px]" />
                    </a>
                </div>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4 text-sm uppercase tracking-wider">Menu</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="/" class="hover:text-white transition-colors">Beranda</a></li>
                    <li><a href="#fitur" class="hover:text-white transition-colors">Fitur</a></li>
                    <li><a href="#harga" class="hover:text-white transition-colors">Harga</a></li>
                    <li><a href="/docs" class="hover:text-white transition-colors">Dokumentasi</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4 text-sm uppercase tracking-wider">Akses</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="/admin/login" class="hover:text-white transition-colors">Login Admin</a></li>
                    <li><a href="/docs" class="hover:text-white transition-colors">Tutorial Lengkap</a></li>
                    <li><a href="/sitemap.xml" class="hover:text-white transition-colors">Sitemap</a></li>
                    <li><a href="#demo" class="hover:text-white transition-colors">Akun Demo</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4 text-sm uppercase tracking-wider">Legal</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="#" class="hover:text-white transition-colors">Kebijakan Privasi</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Syarat &amp; Ketentuan</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Kontak</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
            <p>&copy; {{ date('Y') }} {{ $appName }}. Seluruh hak cipta dilindungi.</p>
            <p class="text-gray-500">Dibangun dengan <span class="text-red-400">&hearts;</span> di Indonesia</p>
        </div>
    </div>
</footer>

@include('components.purchase-cta')

{{-- SCRIPTS --}}
<script>
(function() {
    // Particle effect in hero
    var container = document.getElementById('particles');
    if (container) {
        var colors = ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#e0e7ff', '#a78bfa'];
        for (var i = 0; i < 30; i++) {
            var p = document.createElement('div');
            p.className = 'particle';
            var size = Math.random() * 4 + 1;
            p.style.width = size + 'px';
            p.style.height = size + 'px';
            p.style.left = Math.random() * 100 + '%';
            p.style.top = Math.random() * 100 + '%';
            p.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            p.style.animationDuration = (Math.random() * 10 + 8) + 's';
            p.style.animationDelay = (Math.random() * 5) + 's';
            container.appendChild(p);
        }
    }

    // Counter animation on scroll
    var counters = document.querySelectorAll('.counter');
    var counted = {};
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting && !counted[entry.target.dataset.target]) {
                counted[entry.target.dataset.target] = true;
                var el = entry.target;
                var target = parseInt(el.dataset.target);
                var duration = 1500;
                var step = target / (duration / 16);
                var current = 0;
                var timer = setInterval(function() {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    el.textContent = Math.floor(current);
                    if (el.dataset.target == '50') el.textContent = Math.floor(current) + 'k';
                }, 16);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(function(c) { observer.observe(c); });

    // Initial trigger for visible counters
    setTimeout(function() {
        counters.forEach(function(c) {
            var rect = c.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0 && !counted[c.dataset.target]) {
                counted[c.dataset.target] = true;
                var target = parseInt(c.dataset.target);
                var duration = 1500;
                var step = target / (duration / 16);
                var current = 0;
                var timer = setInterval(function() {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    c.textContent = Math.floor(current);
                    if (c.dataset.target == '50') c.textContent = Math.floor(current) + 'k';
                }, 16);
            }
        });
    }, 500);
})();
</script>

@include('components.purchase-cta')

</body>
</html>
