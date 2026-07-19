<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500,700" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        code, pre { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-extrabold text-slate-900">{{ config('app.name') }} — API</h1>
            <p class="text-slate-500 mt-2">Base URL: <code class="bg-slate-200 px-2 py-0.5 rounded text-sm">{{ config('app.url') }}/api/v1</code></p>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-8">
            <div class="font-semibold text-amber-800 mb-2">Authentication</div>
            <p class="text-amber-700 text-sm">Semua endpoint (kecuali login & webhooks) menggunakan <strong>Laravel Sanctum</strong> token. Header: <code class="bg-amber-100 px-1.5 py-0.5 rounded">Authorization: Bearer {token}</code></p>
            <p class="text-amber-700 text-sm mt-1">Rate limit: login 10/min, API 120/min, webhooks 30/min</p>
        </div>

        @foreach($grouped as $group => $routes)
            @if($routes->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-xl font-bold text-slate-800 mb-4 border-b-2 border-indigo-500 pb-1 inline-block">{{ $group }}</h2>
                <div class="space-y-3">
                    @foreach($routes as $route)
                    <div class="bg-white rounded-lg border border-slate-200 p-4 hover:shadow-md transition">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase
                                @if(str_contains($route['method'], 'GET')) bg-green-100 text-green-700
                                @elseif(str_contains($route['method'], 'POST')) bg-blue-100 text-blue-700
                                @elseif(str_contains($route['method'], 'PUT')) bg-amber-100 text-amber-700
                                @elseif(str_contains($route['method'], 'DELETE')) bg-red-100 text-red-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ $route['method'] }}
                            </span>
                            <code class="text-sm text-slate-800 font-medium">{{ $route['uri'] }}</code>
                        </div>
                        @if(!empty($route['middleware']))
                        <div class="flex gap-1 flex-wrap">
                            @foreach($route['middleware'] as $m)
                            <span class="text-xs bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">{{ $m }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach

        <div class="bg-slate-800 text-white rounded-xl p-6 mt-10 text-center">
            <p class="text-slate-400 text-sm">Dokumentasi diperbarui otomatis dari routes aplikasi.</p>
        </div>
    </div>
</body>
</html>
