<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak — {{ config('app.name') }}</title>
    <meta name="description" content="Hubungi kami untuk informasi lebih lanjut tentang aplikasi POS Retail.">
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
            <h1 class="text-4xl font-extrabold mb-4">Hubungi Kami</h1>
            <p class="text-blue-200 text-lg">Punya pertanyaan atau butuh bantuan? Kami siap membantu.</p>
        </div>
    </section>

    <div class="max-w-4xl mx-auto px-4 py-16">
        <div class="grid md:grid-cols-2 gap-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <div class="text-4xl mb-4">💬</div>
                <h3 class="text-xl font-bold mb-2">WhatsApp</h3>
                <p class="text-slate-500 mb-4">Respon cepat via WhatsApp. Cocok untuk pertanyaan singkat atau request demo.</p>
                <a href="https://wa.me/6281234567890" target="_blank" class="inline-flex items-center gap-2 px-5 py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition">
                    Chat WhatsApp &rarr;
                </a>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <div class="text-4xl mb-4">📧</div>
                <h3 class="text-xl font-bold mb-2">Email</h3>
                <p class="text-slate-500 mb-4">Untuk pertanyaan teknis, partnership, atau pembelian source code.</p>
                <a href="mailto:hello@pos-retail.test" class="inline-flex items-center gap-2 text-blue-600 font-semibold hover:underline">
                    hello@pos-retail.test &rarr;
                </a>
            </div>
        </div>

        <div class="mt-12 bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <h3 class="text-xl font-bold mb-6">Atau kunjungi halaman lainnya:</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <a href="/docs" class="px-4 py-3 bg-slate-50 rounded-xl text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition text-center">Dokumentasi</a>
                <a href="{{ route('blog.index') }}" class="px-4 py-3 bg-slate-50 rounded-xl text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition text-center">Blog</a>
                <a href="{{ route('faq') }}" class="px-4 py-3 bg-blue-50 rounded-xl text-sm font-medium text-blue-600 transition text-center">FAQ</a>
                <a href="{{ route('login') }}" class="px-4 py-3 bg-slate-50 rounded-xl text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition text-center">Demo</a>
            </div>
        </div>
    </div>

    <footer class="bg-slate-900 text-slate-400 py-10 text-sm">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }} &middot; Powered by Laravel</p>
        </div>
    </footer>
</body>
</html>
