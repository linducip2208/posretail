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
        "@type": "ItemList",
        "itemListElement": [
            @foreach($products as $i => $product)
            {
                "@type": "ListItem",
                "position": {{ $i + 1 }},
                "item": {
                    "@type": "Product",
                    "name": "{{ $product->name }}",
                    "offers": {
                        "@type": "Offer",
                        "price": "{{ number_format($product->selling_price, 0, '.', '') }}",
                        "priceCurrency": "IDR"
                    }
                }
            }@if(!$loop->last),@endif
            @endforeach
        ]
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
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Daftar Harga {{ $category->name }}</h1>
        <p class="text-gray-600 mb-8">Daftar harga {{ $category->name }} terbaru hari ini. Harga eceran, grosir, dan member. Update harga real-time.</p>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-blue-50 text-left">
                        <th class="px-6 py-3 font-semibold text-gray-700 uppercase text-xs tracking-wider">Nama Produk</th>
                        <th class="px-6 py-3 font-semibold text-gray-700 uppercase text-xs tracking-wider text-right">Harga Eceran</th>
                        <th class="px-6 py-3 font-semibold text-gray-700 uppercase text-xs tracking-wider text-right">Harga Grosir</th>
                        <th class="px-6 py-3 font-semibold text-gray-700 uppercase text-xs tracking-wider text-right">Harga Member</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $product->name }}</td>
                        <td class="px-6 py-4 text-right font-mono text-gray-700">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-mono text-gray-700">Rp {{ number_format($product->wholesale_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-mono text-green-700">Rp {{ number_format($product->member_price, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada produk di kategori ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
    <footer class="bg-gray-800 text-gray-400 text-sm py-6 mt-8">
        <div class="max-w-6xl mx-auto px-4 text-center">
            &copy; {{ date('Y') }} POS Retail. Semua harga dapat berubah sewaktu-waktu.
        </div>
    </footer>
</body>
</html>
