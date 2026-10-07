{{-- Branded Two-Column Login — POS Retail --}}
@php
    $brandName = \App\Models\SystemSetting::getAppName();
    $logoUrl = \App\Models\SystemSetting::getLogoUrl();
    $demoAccounts = [
        ['role' => 'Owner', 'email' => 'owner@pos-retail.test', 'password' => 'password'],
        ['role' => 'Manager', 'email' => 'manager@pos-retail.test', 'password' => 'password'],
        ['role' => 'Admin', 'email' => 'admin@pos-retail.test', 'password' => 'password'],
        ['role' => 'Kasir', 'email' => 'kasir@pos-retail.test', 'password' => 'password'],
        ['role' => 'Gudang', 'email' => 'gudang@pos-retail.test', 'password' => 'password'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — {{ $brandName }}</title>
    @vite('resources/css/portal.css')
    <style>
        .animate-fade-slide { animation: fadeSlideUp .7s cubic-bezier(.16,1,.3,1) both; }
        .animate-fade-slide:nth-child(2) { animation-delay: .15s; }
        .animate-fade-slide:nth-child(3) { animation-delay: .3s; }
        @keyframes fadeSlideUp {
            0% { transform: translateY(30px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        @keyframes floatSlow {
            0%,100% { transform: translateY(0) }
            50% { transform: translateY(-12px) }
        }
        .animate-float-slow { animation: floatSlow 5s ease-in-out infinite; }
    </style>
</head>
<body class="font-sans bg-slate-50 antialiased">
    <div class="min-h-screen grid lg:grid-cols-2 gap-0">
        {{-- Left: Hero Brand Panel --}}
        <div class="hidden lg:flex relative bg-gradient-to-br from-[#1a569d] via-[#1a569d] to-slate-900 p-12 flex-col justify-between overflow-hidden">
            <div class="absolute inset-0 opacity-30"
                 style="background-image: radial-gradient(circle at 30% 20%, rgba(59,130,246,.4), transparent 50%), radial-gradient(circle at 70% 80%, rgba(37,99,235,.3), transparent 50%);"></div>
            <div class="absolute -bottom-20 -right-20 opacity-10"><x-ti name="building-store" class="w-[20rem] h-[20rem] text-white" /></div>

            <div class="relative">
                <a href="{{ route('home') }}" class="flex items-center gap-3 text-white">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="h-9 w-auto brightness-0 invert">
                    @else
                        <div class="w-10 h-10 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center"><x-ti name="building-store" class="w-6 h-6 text-white" /></div>
                        <span class="font-semibold text-xl tracking-tight">{{ $brandName }}</span>
                    @endif
                </a>
            </div>

            <div class="relative text-white">
                <h2 class="text-5xl font-extrabold leading-tight mb-4">Kelola Bisnis Retail<br>Dalam Satu Aplikasi</h2>
                <p class="text-blue-200 text-lg leading-relaxed mb-10 max-w-md">Point of Sale lengkap dengan inventori, pembelian, laporan keuangan, dan program loyalitas pelanggan.</p>
                <div class="grid grid-cols-3 gap-4 max-w-md">
                    <div class="bg-white/10 backdrop-blur p-4 rounded-2xl animate-fade-slide">
                        <div class="mb-1 text-white"><x-ti name="shopping-cart" class="w-8 h-8" /></div>
                        <div class="text-xs font-medium">POS Kasir</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur p-4 rounded-2xl animate-fade-slide">
                        <div class="mb-1 text-white"><x-ti name="chart-bar" class="w-8 h-8" /></div>
                        <div class="text-xs font-medium">Laporan</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur p-4 rounded-2xl animate-fade-slide">
                        <div class="mb-1 text-white"><x-ti name="star-filled" class="w-8 h-8" /></div>
                        <div class="text-xs font-medium">Loyalitas</div>
                    </div>
                </div>
            </div>

            <div class="relative text-[#7fb3e8]/70 text-xs">
                &copy; {{ date('Y') }} {{ $brandName }} &middot; Powered by Laravel
            </div>
        </div>

        {{-- Right: Login Form --}}
        <div class="flex items-center justify-center p-8 lg:p-16">
            <div class="w-full max-w-md">
                <h1 class="text-4xl font-extrabold text-slate-900 mb-2">Masuk</h1>
                <p class="text-slate-500 mb-8">Belum punya akun? <a href="{{ route('register') }}" class="text-[#206bc4] font-semibold hover:underline">Daftar gratis</a></p>

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-[#d63939]/8 border border-red-200 rounded-xl text-sm text-[#b22b2b]">
                        @foreach ($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="email@example.com"
                               class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-4 focus:ring-[#206bc4]/10 focus:border-[#206bc4] transition outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                        <input type="password" name="password" required
                               placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                               class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-4 focus:ring-[#206bc4]/10 focus:border-[#206bc4] transition outline-none">
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-[#206bc4] focus:ring-[#206bc4]">
                            Ingatkan saya
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sm text-[#206bc4] font-semibold hover:underline">Lupa password?</a>
                    </div>
                    <button type="submit" class="w-full py-3.5 bg-[#206bc4] hover:bg-[#1a569d] text-white font-semibold rounded-lg shadow-sm active:scale-[0.98] transition min-h-[48px]">
                        Masuk &rarr;
                    </button>
                </form>

                <div class="my-8 flex items-center gap-3">
                    <div class="flex-1 h-px bg-slate-200"></div>
                    <span class="text-xs text-slate-400 uppercase tracking-wider">atau</span>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm">
                    <div class="font-semibold text-slate-800 mb-2">Demo Login</div>
                    <div class="space-y-1 text-slate-600 text-xs font-mono">
                        @foreach($demoAccounts as $account)
                            <div><span class="font-bold">{{ $account['role'] }}:</span> {{ $account['email'] }} / {{ $account['password'] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
