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
    <style>body{font-family:Inter,sans-serif}</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-blue-600 text-white">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="font-bold text-lg">POS Retail</a>
            <div class="flex gap-4 text-sm">
                <a href="/" class="hover:text-blue-200">Beranda</a>
                <a href="/kategori" class="hover:text-blue-200">Kategori</a>
                <a href="/sitemap" class="hover:text-blue-200">Sitemap</a>
            </div>
        </div>
    </nav>
    <main class="max-w-6xl mx-auto px-4 py-8">
        @yield('content')
    </main>
    <footer class="bg-gray-800 text-gray-400 text-sm py-6 mt-8">
        <div class="max-w-6xl mx-auto px-4 text-center">
            &copy; {{ date('Y') }} POS Retail. Semua harga dapat berubah sewaktu-waktu.
        </div>
    </footer>
</body>
</html>
