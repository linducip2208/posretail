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
    <meta name="twitter:title" content="{{ $seoMeta['title'] }}">
    <meta name="twitter:description" content="{{ $seoMeta['description'] }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif}</style>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "ItemList",
        "itemListElement": [
            @foreach($alternatives as $i => $alt)
            {
                "@type": "ListItem",
                "position": {{ $i + 1 }},
                "item": {
                    "@type": "Product",
                    "name": "{{ $alt->name }}",
                    "offers": {
                        "@type": "Offer",
                        "price": "{{ number_format($alt->selling_price, 0, '.', '') }}",
                        "priceCurrency": "IDR"
                    }
                }
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
    </script>
</head>
<body class="bg-gray-50">
    <header class="bg-white border-b sticky top-0 z-50 backdrop-blur-xl bg-white/80">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/" class="text-xl font-bold text-indigo-600">POS Retail</a>
            <nav class="space-x-4 text-sm">
                <a href="/" class="text-gray-600 hover:text-indigo-600">Beranda</a>
                <a href="/docs" class="text-gray-600 hover:text-indigo-600">Dokumentasi</a>
            </nav>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Alternatif {{ $product->name }}</h1>
        <p class="text-gray-500 mb-10">Produk serupa dengan {{ $product->name }} yang mungkin lebih cocok dengan budget dan kebutuhan Anda.</p>

        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 mb-10">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-xs font-semibold bg-indigo-600 text-white px-2 py-1 rounded">REFERENSI</span>
                <span class="text-sm font-mono text-indigo-400">{{ $product->sku }}</span>
            </div>
            <h2 class="text-xl font-bold text-gray-900">{{ $product->name }}</h2>
            <p class="text-gray-600 text-sm mt-1">{{ Str::limit($product->description, 200) }}</p>
            <p class="text-lg font-extrabold text-indigo-600 mt-3">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
        </div>

        @if($alternatives->isEmpty())
            <div class="text-center py-20 text-gray-400">
                <p class="text-lg">Belum ada produk alternatif.</p>
                <a href="/" class="mt-4 inline-block text-indigo-600 hover:underline">Kembali ke Beranda</a>
            </div>
        @else
            <h2 class="text-xl font-bold text-gray-900 mb-6">{{ $alternatives->count() }} Alternatif</h2>
            <div class="grid md:grid-cols-2 gap-6">
                @foreach($alternatives as $alt)
                <div class="bg-white rounded-2xl p-6 border hover:shadow-lg transition-shadow">
                    <h3 class="text-lg font-bold text-gray-900">{{ $alt->name }}</h3>
                    <p class="text-sm text-gray-500 mt-1 mb-4">{{ Str::limit($alt->description, 100) }}</p>
                    <p class="text-2xl font-extrabold text-indigo-600">Rp {{ number_format($alt->selling_price, 0, ',', '.') }}</p>
                    @if($alt->selling_price < $product->selling_price)
                        <span class="inline-block mt-2 text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-full">Lebih hemat Rp {{ number_format($product->selling_price - $alt->selling_price, 0, ',', '.') }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </main>

    <footer class="text-center py-8 text-sm text-gray-400 border-t">
        POS Retail &copy; {{ date('Y') }}.
    </footer>
</body>
</html>
