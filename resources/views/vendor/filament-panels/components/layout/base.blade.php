@props([
    'livewire' => null,
])

@php
    $renderHookScopes = $livewire?->getRenderHookScopes();
@endphp

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ __('filament-panels::layout.direction') ?? 'ltr' }}"
    @class([
        'fi',
        'dark' => filament()->hasDarkMode() && filament()->hasDarkModeForced(),
    ])
>
    <head>
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::HEAD_START, scopes: $renderHookScopes) }}

        <meta charset="utf-8" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
        {{-- Kunci skema terang: cegah auto-darken browser HP (teks putih tak terbaca). --}}
        <meta name="color-scheme" content="light" />

        <link rel="manifest" href="/manifest.json" />
        <meta name="theme-color" content="#4f46e5" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
        <meta name="apple-mobile-web-app-title" content="POS Retail" />
        <link rel="apple-touch-icon" href="/pwa/apple-touch-icon.png" />

        @if ($favicon = filament()->getFavicon())
            <link rel="icon" href="{{ $favicon }}" />
        @endif

        @php
            $title = trim(strip_tags($livewire?->getTitle() ?? ''));
            $brandName = trim(strip_tags(filament()->getBrandName()));
        @endphp

        <title>
            {{ filled($title) ? $title : null }}
            {{ filled($brandName) && filled($title) ? ' - ' : null }}
            {{ filled($brandName) ? $brandName : null }}
        </title>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::STYLES_BEFORE, scopes: $renderHookScopes) }}

        <style>
            [x-cloak=''],
            [x-cloak='x-cloak'],
            [x-cloak='1'] {
                display: none !important;
            }

            [x-cloak='inline-flex'] {
                display: inline-flex !important;
            }

            @media (max-width: 1023px) {
                [x-cloak='-lg'] {
                    display: none !important;
                }
            }

            @media (min-width: 1024px) {
                [x-cloak='lg'] {
                    display: none !important;
                }
            }
        </style>

        @filamentStyles

        {{ filament()->getTheme()->getHtml() }}
        {{ filament()->getFontPreloadHtml() }}
        {{ filament()->getMonoFontPreloadHtml() }}
        {{ filament()->getSerifFontPreloadHtml() }}
        {{ filament()->getFontHtml() }}
        {{ filament()->getMonoFontHtml() }}
        {{ filament()->getSerifFontHtml() }}

        <style>
            :root {
                --font-family: '{!! filament()->getFontFamily() !!}';
                --mono-font-family: '{!! filament()->getMonoFontFamily() !!}';
                --serif-font-family: '{!! filament()->getSerifFontFamily() !!}';
                --sidebar-width: {{ filament()->getSidebarWidth() }};
                --collapsed-sidebar-width: {{ filament()->getCollapsedSidebarWidth() }};
                --default-theme-mode: {{ filament()->getDefaultThemeMode()->value }};
            }

            html.fi {
                --livewire-progress-bar-color: var(--primary-500);
            }
        </style>

        @stack('styles')

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::STYLES_AFTER, scopes: $renderHookScopes) }}

        @if (! filament()->hasDarkMode())
            <script>
                localStorage.setItem('theme', 'light')
            </script>
        @elseif (filament()->hasDarkModeForced())
            <script>
                localStorage.setItem('theme', 'dark')
            </script>
        @else
            <script>
                const loadDarkMode = () => {
                    window.theme = localStorage.getItem('theme') ?? @js(filament()->getDefaultThemeMode()->value)

                    if (
                        window.theme === 'dark' ||
                        (window.theme === 'system' &&
                            window.matchMedia('(prefers-color-scheme: dark)')
                                .matches)
                    ) {
                        document.documentElement.classList.add('dark')
                    }
                }

                loadDarkMode()

                document.addEventListener('livewire:navigated', loadDarkMode)
            </script>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::HEAD_END, scopes: $renderHookScopes) }}
    </head>

    <body
        {{
            $attributes
                ->merge($livewire?->getExtraBodyAttributes() ?? [], escape: false)
                ->class([
                    'fi-body',
                    'fi-panel-' . filament()->getId(),
                ])
        }}
    >
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::BODY_START, scopes: $renderHookScopes) }}

        {{ $slot }}

        @livewire(Filament\Livewire\Notifications::class)

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SCRIPTS_BEFORE, scopes: $renderHookScopes) }}

        @filamentScripts(withCore: true)

        @if (filament()->hasBroadcasting() && config('filament.broadcasting.echo'))
            <script data-navigate-once>
                window.Echo = new window.EchoFactory(@js(config('filament.broadcasting.echo')))

                window.dispatchEvent(new CustomEvent('EchoLoaded'))
            </script>
        @endif

        @if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
            <script>
                loadDarkMode()
            </script>
        @endif

        @stack('scripts')

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SCRIPTS_AFTER, scopes: $renderHookScopes) }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::BODY_END, scopes: $renderHookScopes) }}

        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js').then(function(reg) {
                    console.log('PWA Service Worker registered');
                }).catch(function(err) {
                    console.log('PWA Service Worker failed:', err);
                });
            }
        </script>

        {{-- Boot guard HP: kalau Alpine/Livewire gagal boot (cache korup, JS terpotong
             di jaringan lemah), halaman akan putih selamanya karena x-cloak.
             Tampilkan tombol pulihkan: buang SW + cache lalu muat ulang bersih. --}}
        <div id="fi-boot-guard" style="display:none;position:fixed;left:12px;right:12px;bottom:12px;z-index:9999;background:#0B2854;color:#fff;border-radius:12px;padding:14px 16px;box-shadow:0 8px 28px rgba(0,0,0,.35);font:14px/1.5 system-ui,sans-serif">
            <div style="font-weight:700;margin-bottom:4px">Halaman tidak termuat sempurna</div>
            <div style="opacity:.8;margin-bottom:10px">Kemungkinan cache lama/korup. Ketuk tombol untuk membersihkan &amp; memuat ulang.</div>
            <button id="fi-boot-guard-btn" style="background:#0878F9;color:#fff;border:0;border-radius:8px;padding:10px 18px;font-weight:700;min-height:44px">Bersihkan &amp; Muat Ulang</button>
        </div>
        <script>
            (function () {
                function booted() {
                    return !!(window.Alpine && window.Livewire);
                }
                function showGuard() {
                    var el = document.getElementById('fi-boot-guard');
                    if (el) el.style.display = 'block';
                }
                document.getElementById('fi-boot-guard-btn')?.addEventListener('click', function () {
                    var done = function () { window.location.reload(); };
                    try {
                        var p = Promise.resolve();
                        if ('serviceWorker' in navigator) {
                            p = p.then(function () { return navigator.serviceWorker.getRegistrations(); })
                                .then(function (rs) { return Promise.all(rs.map(function (r) { return r.unregister(); })); });
                        }
                        if ('caches' in window) {
                            p = p.then(function () { return caches.keys(); })
                                .then(function (ks) { return Promise.all(ks.map(function (k) { return caches.delete(k); })); });
                        }
                        p.then(done).catch(done);
                        setTimeout(done, 3000);
                    } catch (e) { done(); }
                });
                if (document.readyState === 'complete' ? booted() : false) return;
                var waited = 0;
                var timer = setInterval(function () {
                    waited += 1000;
                    if (booted()) { clearInterval(timer); return; }
                    if (waited >= 12000) { clearInterval(timer); showGuard(); }
                }, 1000);
                window.addEventListener('load', function () {
                    setTimeout(function () { if (!booted()) showGuard(); }, 12000);
                });
            })();
        </script>
    </body>
</html>
