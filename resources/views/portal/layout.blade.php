<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Pelanggan') — POS Retail</title>
    @vite('resources/css/portal.css')
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen font-sans antialiased">

    <header class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('portal.index') }}" class="text-lg font-bold text-[#206bc4]">POS Retail</a>
            <div class="flex items-center gap-4">
                @auth('customer')
                    <span class="text-sm text-gray-600">{{ auth('customer')->user()->name }}</span>
                    <form action="{{ route('portal.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-400 hover:text-[#d63939] transition">
                            Keluar
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 mt-16">
        <div class="max-w-4xl mx-auto px-4 py-6 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} POS Retail. Seluruh hak cipta dilindungi.
        </div>
    </footer>

</body>
</html>
