<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoTitle ?? 'Blog — ' . config('app.name') }}</title>
    <meta name="description" content="{{ $seoDescription ?? 'Tips bisnis retail dan panduan manajemen toko Indonesia.' }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe',
                            300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6',
                            600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af',
                        },
                    },
                },
            },
        }
    </script>
</head>
<body class="font-sans bg-stone-50 text-slate-800 antialiased">
    {{-- Navbar --}}
    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-xl text-slate-900">
                <span class="text-2xl">🏪</span> {{ config('app.name') }}
            </a>
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="text-sm text-slate-600 hover:text-blue-600 transition">Beranda</a>
                <a href="{{ route('blog.index') }}" class="text-sm text-blue-600 font-semibold">Blog</a>
                <a href="/docs" class="text-sm text-slate-600 hover:text-blue-600 transition">Dokumentasi</a>
                <a href="{{ route('login') }}" class="text-sm px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Masuk</a>
            </div>
        </div>
    </nav>

    {{-- Blog Header --}}
    <section class="bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 text-white py-16">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <h1 class="text-4xl font-extrabold mb-4">{{ $category->name ?? 'Blog' }}</h1>
            <p class="text-blue-200 text-lg max-w-2xl mx-auto">Tips bisnis retail, strategi penjualan, panduan manajemen toko, dan berita terbaru seputar dunia retail Indonesia.</p>
            @if(isset($category))
                <p class="text-blue-300 text-sm mt-2">Kategori: {{ $category->name }}</p>
            @endif
        </div>
    </section>

    {{-- Search --}}
    <div class="max-w-6xl mx-auto px-4 -mt-6 mb-10">
        <form method="GET" action="{{ route('blog.index') }}" class="bg-white rounded-2xl shadow-lg p-1.5 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari artikel..."
                   class="flex-1 px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none text-sm">
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold text-sm hover:bg-blue-700 transition">Cari</button>
        </form>
    </div>

    <div class="max-w-6xl mx-auto px-4 pb-20">
        <div class="grid lg:grid-cols-4 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-3">
                @if($posts->count())
                    <div class="grid md:grid-cols-2 gap-6">
                        @foreach($posts as $post)
                            <a href="{{ route('blog.show', $post->slug) }}" class="group bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md hover:-translate-y-1 transition">
                                @if($post->featured_image)
                                    <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center text-5xl">📝</div>
                                @endif
                                <div class="p-5">
                                    <span class="text-xs text-blue-600 font-semibold uppercase tracking-wider">{{ $post->category?->name ?? 'Umum' }}</span>
                                    <h3 class="font-bold text-lg mt-1 group-hover:text-blue-600 transition line-clamp-2">{{ $post->title }}</h3>
                                    <p class="text-sm text-slate-500 mt-2 line-clamp-2">{{ $post->excerpt }}</p>
                                    <div class="flex items-center justify-between mt-4 text-xs text-slate-400">
                                        <span>{{ $post->published_at?->format('d M Y') }}</span>
                                        <span>{{ $post->author?->name ?? config('app.name') }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-10">
                        {{ $posts->links() }}
                    </div>
                @else
                    <div class="text-center py-20">
                        <div class="text-6xl mb-4">📭</div>
                        <h3 class="text-xl font-bold text-slate-700 mb-2">Belum ada artikel</h3>
                        <p class="text-slate-500">Artikel blog akan segera hadir. Kunjungi lagi nanti!</p>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Categories --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <h4 class="font-bold text-sm uppercase tracking-wider text-slate-500 mb-4">Kategori</h4>
                    <div class="space-y-1">
                        @foreach($categories as $cat)
                            <a href="{{ route('blog.category', $cat->slug) }}"
                               class="flex items-center justify-between px-3 py-2 rounded-lg text-sm hover:bg-blue-50 hover:text-blue-600 transition {{ isset($category) && $category->id == $cat->id ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600' }}">
                                <span>{{ $cat->name }}</span>
                                <span class="text-xs bg-slate-100 px-2 py-0.5 rounded-full">{{ $cat->posts_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Recent Posts --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <h4 class="font-bold text-sm uppercase tracking-wider text-slate-500 mb-4">Terbaru</h4>
                    <div class="space-y-3">
                        @foreach($recentPosts as $rp)
                            <a href="{{ route('blog.show', $rp->slug) }}" class="block group">
                                <h5 class="text-sm font-semibold text-slate-700 group-hover:text-blue-600 transition line-clamp-2">{{ $rp->title }}</h5>
                                <span class="text-xs text-slate-400">{{ $rp->published_at?->format('d M Y') }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- CTA Source Code --}}
                <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl shadow-lg p-6 text-white text-center">
                    <div class="text-4xl mb-3">💻</div>
                    <h4 class="font-bold mb-2">Butuh Aplikasi POS?</h4>
                    <p class="text-sm text-blue-100 mb-4">Source code POS Retail siap pakai. Integrasi payment gateway, inventori, laporan lengkap.</p>
                    <a href="https://wa.me/6281234567890" target="_blank" class="inline-block w-full py-2.5 bg-white text-blue-700 rounded-xl font-semibold text-sm hover:bg-blue-50 transition">
                        Chat WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="bg-slate-900 text-slate-400 py-10 text-sm">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }} &middot; Powered by Laravel</p>
        </div>
    </footer>
</body>
</html>
