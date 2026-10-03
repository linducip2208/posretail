<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoMeta['title'] }}</title>
    <meta name="description" content="{{ $seoMeta['description'] }}">
    <link rel="canonical" href="{{ $seoMeta['canonical'] }}">
    <meta property="og:title" content="{{ $seoMeta['title'] }}">
    <meta property="og:description" content="{{ $seoMeta['description'] }}">
    <meta property="og:url" content="{{ $seoMeta['canonical'] }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif}</style>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "POS Retail {{ $cityName }}",
        "description": "{{ $seoMeta['description'] }}",
        @if(isset($outlets[0]))
        "address": { "@type": "PostalAddress", "streetAddress": "{{ $outlets[0]->address }}" },
        "telephone": "{{ $outlets[0]->phone }}",
        @endif
        "@id": "{{ $seoMeta['canonical'] }}"
    }
    </script>
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
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Toko Retail di {{ $cityName }}</h1>
        <p class="text-gray-600 mb-8">Kunjungi toko kami di {{ $cityName }}. Produk lengkap, harga terbaik, bisa beli grosir dan eceran.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($outlets as $outlet)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                <h3 class="font-bold text-gray-900 text-lg mb-2">{{ $outlet->name }}</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-start gap-2">
                        <x-ti name="map-pin" class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" />
                        <span>{{ $outlet->address }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-ti name="phone" class="w-4 h-4 text-blue-500 flex-shrink-0" />
                        <span>{{ $outlet->phone }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-ti name="clock" class="w-4 h-4 text-blue-500 flex-shrink-0" />
                        <span>{{ $outlet->active ? 'Buka' : 'Tutup' }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 text-gray-400">
                <p>Belum ada toko di {{ $cityName }}.</p>
                <a href="/" class="text-blue-600 hover:underline mt-2 inline-block">Lihat Semua Toko</a>
            </div>
            @endforelse
        </div>
    </main>
    <footer class="bg-gray-800 text-gray-400 text-sm py-6 mt-8">
        <div class="max-w-6xl mx-auto px-4 text-center">
            &copy; {{ date('Y') }} POS Retail. Semua harga dapat berubah sewaktu-waktu.
        </div>
    </footer>
</body>
</html>
