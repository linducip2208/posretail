<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ — {{ config('app.name') }}</title>
    <meta name="description" content="Pertanyaan yang sering diajukan tentang aplikasi POS Retail.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','ui-sans-serif','system-ui','sans-serif']},colors:{brand:{50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af'}}}}}</script>
</head>
<body class="font-sans bg-stone-50 text-slate-800 antialiased">
    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-xl text-slate-900"><span class="text-2xl">🏪</span> {{ config('app.name') }}</a>
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="text-sm text-slate-600 hover:text-blue-600 transition">Beranda</a>
                <a href="{{ route('blog.index') }}" class="text-sm text-slate-600 hover:text-blue-600 transition">Blog</a>
                <a href="/docs" class="text-sm text-slate-600 hover:text-blue-600 transition">Dokumentasi</a>
                <a href="{{ route('login') }}" class="text-sm px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Masuk</a>
            </div>
        </div>
    </nav>

    <section class="bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 text-white py-16">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h1 class="text-4xl font-extrabold mb-4">FAQ</h1>
            <p class="text-blue-200 text-lg">Pertanyaan yang sering diajukan tentang aplikasi POS Retail.</p>
        </div>
    </section>

    <div class="max-w-3xl mx-auto px-4 py-16">
        @php
        $faqs = [
            ['q' => 'Apa itu POS Retail?', 'a' => 'POS Retail adalah aplikasi Point of Sale lengkap untuk bisnis retail di Indonesia. Mencakup manajemen produk, inventori, transaksi penjualan, pembelian, laporan keuangan, dan program loyalitas pelanggan.'],
            ['q' => 'Apakah bisa multi-outlet?', 'a' => 'Ya, POS Retail mendukung multi-outlet (cabang). Setiap outlet bisa punya stok, karyawan, dan transaksi terpisah namun tetap terintegrasi dalam satu dashboard.'],
            ['q' => 'Payment gateway apa yang didukung?', 'a' => 'POS Retail mendukung berbagai payment gateway via sistem provider dinamis. Anda bisa menambahkan provider sendiri seperti Midtrans, Xendit, Duitku, dan lainnya melalui admin panel.'],
            ['q' => 'Apakah ada fitur loyalitas pelanggan?', 'a' => 'Ya, POS Retail memiliki fitur loyalty points dan membership tier. Pelanggan otomatis mendapat poin setiap transaksi dan bisa naik tier untuk mendapatkan benefit lebih.'],
            ['q' => 'Bagaimana dengan laporan keuangan?', 'a' => 'Tersedia 3 laporan utama: Laporan Penjualan (revenue, top produk), Laporan Keuangan (P&L, cash flow), dan Laporan Stok (nilai inventori, low stock alert). Semua bisa diexport ke PDF.'],
            ['q' => 'Apakah ada API untuk integrasi?', 'a' => 'Ya, tersedia REST API v1 untuk integrasi dengan aplikasi mobile atau third-party. Meliputi produk, kategori, pesanan, pelanggan, dan payment gateway.'],
            ['q' => 'Bagaimana cara instalasi?', 'a' => 'POS Retail adalah aplikasi Laravel standar. Clone repository, jalankan composer install dan npm install, setup .env, migrate database, dan seed demo data. Lihat halaman /docs untuk tutorial lengkap.'],
            ['q' => 'Apakah bisa custom/modifikasi?', 'a' => 'Source code POS Retail bisa dibeli untuk dimodifikasi sesuai kebutuhan bisnis Anda. Hubungi kami via WhatsApp untuk informasi lebih lanjut.'],
        ];
        @endphp
        <div class="space-y-4">
            @foreach($faqs as $faq)
                <details class="group bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <summary class="flex items-center justify-between px-6 py-5 cursor-pointer hover:bg-slate-50 transition">
                        <span class="font-semibold text-slate-800">{{ $faq['q'] }}</span>
                        <svg class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="px-6 pb-5 text-slate-600 leading-relaxed">{{ $faq['a'] }}</div>
                </details>
            @endforeach
        </div>
    </div>

    <footer class="bg-slate-900 text-slate-400 py-10 text-sm">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }} &middot; Powered by Laravel</p>
        </div>
    </footer>
</body>
</html>
