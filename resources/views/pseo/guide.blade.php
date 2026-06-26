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
    <meta property="og:type" content="article">
    <meta name="twitter:card" content="summary_large_image">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif}</style>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "Article",
        "headline": "{{ $seoMeta['title'] }}",
        "description": "{{ $seoMeta['description'] }}",
        "author": { "@type": "Organization", "name": "POS Retail" }
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
    <main class="max-w-4xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">{{ $label }} {{ $category->name }}</h1>
        <p class="text-gray-600 mb-8">{{ $label }} {{ $category->name }} terlengkap. Panduan memilih produk terbaik sesuai budget dan kebutuhan Anda.</p>

        <div class="prose max-w-none bg-white rounded-xl shadow-sm border border-gray-100 p-8 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Panduan {{ $label }} {{ $category->name }}</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
                Kategori {{ $category->name }} memiliki banyak pilihan produk dengan variasi harga dan kualitas.
                {{ $label === 'Tips Memilih' ? 'Berikut tips memilih produk terbaik berdasarkan kebutuhan Anda.' : '' }}
                {{ $label === 'Cara Merawat' ? 'Perawatan yang tepat akan membuat produk lebih awet dan tahan lama.' : '' }}
                {{ $label === 'Kelebihan & Kekurangan' ? 'Setiap produk memiliki karakteristik berbeda. Kenali kelebihan dan kekurangan sebelum membeli.' : '' }}
                {{ $label === 'Perbandingan Harga' ? 'Harga dapat bervariasi antar merek dan tempat. Simak perbandingan harga terbaru.' : '' }}
                {{ $label === 'Review Terbaru' ? 'Review dari pembeli sebelumnya membantu Anda mengambil keputusan yang tepat.' : '' }}
            </p>

            @if($products->isNotEmpty())
            <h3 class="text-lg font-bold text-gray-800 mb-3 mt-6">Produk {{ $category->name }} Populer</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($products as $product)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                    <h4 class="font-semibold text-gray-900">{{ $product->name }}</h4>
                    <p class="text-sm text-gray-500 mt-1">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h3 class="font-bold text-blue-900 mb-2">Butuh Bantuan Memilih?</h3>
            <p class="text-blue-700 text-sm">Hubungi tim kami untuk konsultasi produk terbaik sesuai kebutuhan Anda.</p>
        </div>
    </main>
    <footer class="bg-gray-800 text-gray-400 text-sm py-6 mt-8">
        <div class="max-w-6xl mx-auto px-4 text-center">
            &copy; {{ date('Y') }} POS Retail. Semua harga dapat berubah sewaktu-waktu.
        </div>
    </footer>
</body>
</html>
