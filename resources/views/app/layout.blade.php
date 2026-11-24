<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Retail Dashboard') · {{ config('app.name','POS Retail') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <script>
        (function () {
            try {
                var prefs = JSON.parse(localStorage.getItem("posretail.theme") || "{}");
                var root = document.documentElement;
                var mode = prefs.mode === "system" ? (matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light") : (prefs.mode || "light");
                root.setAttribute("data-bs-theme", mode);
                ["font","density","width","radius","header"].forEach(function (key) {
                    if (prefs[key]) { root.setAttribute("data-" + key, prefs[key]); }
                });
            } catch (error) {}
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('vendor/tabler/css/tabler.min.css') }}">
    <link rel="stylesheet" href="{{ asset('app-assets/vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('app-assets/css/app.css') }}">
    <style>
        .app-shell{min-height:100vh}
        .kpi-value{font-variant-numeric:tabular-nums}
        .table-num{font-variant-numeric:tabular-nums}
        /* Jaga mega-menu di tepi kanan agar tidak terpotong viewport */
        .topbar-nav .nav-item:nth-last-child(-n+2) > .dropdown-menu { left: auto; right: 0; }
        .topbar-nav .mega-menu { max-width: calc(100vw - 1.5rem); }
        @media (min-width: 1200px) {
            .topbar-nav .mega-menu .mega-grid { max-height: calc(100vh - 230px); overflow-y: auto; }
        }
    </style>
    @stack('styles')
</head>
<body data-page="dashboard">
<div class="app-shell">
    <header class="app-topbar">
        <div class="app-container topbar-main">
            <button class="topbar-icon-btn d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Open navigation menu">
                <i class="bi bi-list fs-4"></i>
            </button>
            <a class="topbar-brand" href="{{ url('/app') }}" aria-label="Home">
                <span class="brand-mark"><img src="{{ asset('app-assets/images/logo-mark.svg') }}" alt="" width="34" height="34"></span>
                <span class="brand-text"><span class="brand-name">{{ config('app.name','POS Retail') }}</span><span class="brand-sub">Retail OS</span></span>
            </a>
            <span class="topbar-divider d-none d-md-block" aria-hidden="true"></span>
            <div class="dropdown store-selector d-none d-md-block">
                <button class="store-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="store-icon"><i class="bi bi-shop"></i></span>
                    <span class="store-label"><span>{{ $currentOutlet->name ?? 'Semua Outlet' }}</span><small><span class="live-dot"></span>Open · {{ $storeCount ?? \App\Models\Outlet::where('active',true)->count() }} toko</small></span>
                    <i class="bi bi-chevron-expand store-caret"></i>
                </button>
                <ul class="dropdown-menu store-menu">
                    <li class="dd-heading">Pindah toko</li>
                    @foreach(\App\Models\Outlet::where('active',true)->limit(6)->get() as $o)
                    <li><a class="dropdown-item store-option" href="{{ url('/app?outlet='.$o->id) }}">
                        <span class="dd-icon"><i class="bi bi-shop"></i></span>
                        <span class="flex-grow-1"><strong>{{ $o->name }}</strong><small>{{ $o->code ?? '' }} · {{ $o->address ? \Illuminate\Support\Str::limit($o->address,40) : 'Aktif' }}</small></span>
                    </a></li>
                    @endforeach
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ url('/admin/outlets') }}"><i class="bi bi-gear"></i>Kelola toko</a></li>
                </ul>
            </div>
            <div class="topbar-search d-none d-lg-block" role="search">
                <i class="bi bi-search"></i>
                <input type="search" class="form-control" id="globalSearch" placeholder="Cari produk, pelanggan, order…" autocomplete="off">
                <span class="search-keys"><kbd>Ctrl</kbd><kbd>K</kbd></span>
                <div class="search-results" id="globalSearchResults"></div>
            </div>
            <div class="topbar-actions">
                <span class="register-status d-none d-xxl-inline-flex" title="Status register"><span class="pulse"></span>REG · Open</span>
                <div class="dropdown d-none d-sm-block">
                    <button class="btn-quick" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-plus-lg"></i><span class="d-none d-md-inline">Buat</span></button>
                    <div class="dropdown-menu dropdown-menu-end create-menu">
                        <div class="dd-heading">Buat baru</div>
                        <div class="create-grid">
                            <a class="create-item" href="{{ url('/pos') }}"><span class="create-icon tone-primary"><i class="bi bi-upc-scan"></i></span><strong>Jual</strong><small>Buka kasir</small></a>
                            <a class="create-item" href="{{ url('/admin/products/create') }}"><span class="create-icon tone-cyan"><i class="bi bi-box-seam"></i></span><strong>Produk</strong><small>Tambah katalog</small></a>
                            <a class="create-item" href="{{ url('/admin/purchase-orders/create') }}"><span class="create-icon tone-violet"><i class="bi bi-clipboard-plus"></i></span><strong>PO</strong><small>Order stok</small></a>
                            <a class="create-item" href="{{ url('/admin/customers/create') }}"><span class="create-icon tone-success"><i class="bi bi-person-plus"></i></span><strong>Customer</strong><small>Daftar member</small></a>
                        </div>
                    </div>
                </div>
                <a class="topbar-icon-btn" href="{{ url('/admin/orders') }}" title="Panel admin"><i class="bi bi-grid-1x2"></i></a>
                <button class="topbar-icon-btn" type="button" data-theme-toggle title="Gelap / Terang"><i class="bi bi-moon-stars"></i></button>
                <button class="topbar-icon-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#customizer" aria-controls="customizer" title="Customize"><i class="bi bi-palette2"></i></button>
                <div class="dropdown topbar-profile">
                    <button class="profile-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="profile-avatar"><img src="{{ asset('app-assets/images/avatars/avatar-11.svg') }}" alt=""><span class="presence"></span></span>
                        <span class="profile-name d-none d-xl-block">{{ auth()->user()?->name ?? 'Kasir' }}<small>{{ ucfirst(auth()->user()?->role ?? 'staff') }}</small></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end profile-menu">
                        <div class="profile-card">
                            <span class="profile-avatar lg"><img src="{{ asset('app-assets/images/avatars/avatar-11.svg') }}" alt=""></span>
                            <div class="min-w-0"><strong>{{ auth()->user()?->name }}</strong><small>{{ auth()->user()?->email }}</small></div>
                        </div>
                        <div class="profile-links">
                            <a class="dropdown-item" href="{{ url('/admin/orders') }}"><i class="bi bi-speedometer2"></i>Panel admin</a>
                            <a class="dropdown-item" href="{{ url('/pos') }}"><i class="bi bi-upc-scan"></i>Buka POS</a>
                            <form method="POST" action="{{ url('/admin/logout') }}" class="d-inline">@csrf<button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right"></i>Keluar</button></form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('app.partials.nav')
    </header>
    <main id="main" class="app-main">
        <div class="app-container">
            @if(session('success'))
            <div class="alert-strip is-success mb-3 py-2"><i class="bi bi-check-circle"></i><span class="small flex-grow-1">{{ session('success') }}</span></div>
            @endif
            @if(session('error'))
            <div class="alert-strip is-danger mb-3 py-2"><i class="bi bi-exclamation-triangle"></i><span class="small flex-grow-1">{{ session('error') }}</span></div>
            @endif
            @yield('content')
        </div>
    </main>
    @include('app.partials.footer')
</div>
@include('app.partials.mobile-nav')
@include('app.partials.customizer')
<script src="{{ asset('app-assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('app-assets/vendor/chart.js/chart.umd.min.js') }}"></script>
<script>
/* Fallback gambar produk yang hilang di storage: ganti placeholder inline (sekali saja per <img>). */
document.addEventListener('error', function (e) {
    var t = e.target;
    if (t && t.tagName === 'IMG' && !t.dataset.fbk) {
        t.dataset.fbk = '1';
        t.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96"><rect width="96" height="96" rx="14" fill="#EEF2FF"/><g fill="none" stroke="#4F46E5" stroke-width="3"><rect x="26" y="30" width="44" height="36" rx="6"/><circle cx="38" cy="42" r="3" fill="#4F46E5" stroke="none"/><path d="M26 58l12-12 8 8 8-8 16 12"/></g></svg>');
    }
}, true);
</script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {});
        });
    }
</script>
<script>
(function () {
    /* Customizer: terapkan preferensi + wire kontrol [data-cz-*]. */
    var KEY = 'posretail.theme';
    function read() { try { return JSON.parse(localStorage.getItem(KEY) || '{}'); } catch (e) { return {}; } }
    function save(p) { try { localStorage.setItem(KEY, JSON.stringify(p)); } catch (e) {} }
    function hexToRgb(h) {
        h = String(h || '').replace('#', '');
        if (h.length === 3) h = h.split('').map(function (c) { return c + c; }).join('');
        var n = parseInt(h, 16);
        return isNaN(n) ? null : [(n >> 16) & 255, (n >> 8) & 255, n & 255];
    }
    function apply(p) {
        var root = document.documentElement;
        if (p.accent) {
            var rgb = hexToRgb(p.accent);
            if (rgb) {
                root.style.setProperty('--app-primary', p.accent);
                root.style.setProperty('--app-primary-rgb', rgb.join(', '));
                root.style.setProperty('--app-primary-hover', p.accent);
            }
        } else { root.style.removeProperty('--app-primary'); root.style.removeProperty('--app-primary-rgb'); root.style.removeProperty('--app-primary-hover'); }
        ['font', 'density', 'width', 'radius', 'header'].forEach(function (k) {
            if (p[k]) root.setAttribute('data-' + k, p[k]); else root.removeAttribute('data-' + k);
        });
        if (p.font === 'jakarta') document.body.style.fontFamily = "'Plus Jakarta Sans','Inter',system-ui,sans-serif";
        else if (p.font === 'manrope') document.body.style.fontFamily = "'Manrope','Inter',system-ui,sans-serif";
        else if (p.font === 'system') document.body.style.fontFamily = "system-ui,-apple-system,'Segoe UI',sans-serif";
        else document.body.style.fontFamily = '';
    }
    var prefs = read();
    apply(prefs);
    function set(k, v) { prefs[k] = v; save(prefs); apply(prefs); }
    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-cz-mode],[data-cz-preset],[data-cz-header],[data-cz-font],[data-cz-density],[data-cz-width],[data-cz-radius],[data-cz-reset]');
        if (!t) return;
        if (t.hasAttribute('data-cz-reset')) {
            prefs = {}; try { localStorage.removeItem(KEY); } catch (err) {}
            var m = (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', m);
            apply(prefs); return;
        }
        if (t.hasAttribute('data-cz-mode')) {
            var mode = t.getAttribute('data-cz-mode');
            if (mode === 'system') mode = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', mode);
            prefs.mode = t.getAttribute('data-cz-mode'); save(prefs); markActive(); return;
        }
        ['preset:accent', 'header:header', 'font:font', 'density:density', 'width:width', 'radius:radius'].forEach(function (pair) {
            var parts = pair.split(':'), attr = 'data-cz-' + parts[0], key = parts[1];
            if (t.hasAttribute(attr)) set(key, t.getAttribute(attr));
        });
    });
    document.addEventListener('change', function (e) {
        var t = e.target.closest('[data-cz-custom]');
        if (t && t.value) set('accent', t.value);
    });
    function norm(v) { return String(v == null ? '' : v); }
    var CZMAP = [['data-cz-mode','mode'], ['data-cz-preset','accent'], ['data-cz-header','header'], ['data-cz-font','font'], ['data-cz-density','density'], ['data-cz-width','width'], ['data-cz-radius','radius']];
    function markActive() {
        var p = read();
        if (!p.mode) { try { p.mode = JSON.parse(localStorage.getItem(KEY) || '{}').mode || 'light'; } catch (e) {} }
        CZMAP.forEach(function (pair) {
            document.querySelectorAll('[' + pair[0] + ']').forEach(function (b) {
                var cur = pair[1] === 'mode' ? (p.mode || 'light') : (p[pair[1]] || '');
                var on = norm(cur) === norm(b.getAttribute(pair[0]));
                b.classList.toggle('is-active', on);
                b.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
        });
    }
    markActive();
    document.addEventListener('alpine:initialized', markActive);
})();
</script>
@stack('scripts')
</body>
</html>
