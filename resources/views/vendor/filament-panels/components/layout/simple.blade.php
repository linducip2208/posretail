@php
    use Filament\Support\Enums\Width;

    $livewire ??= null;

    $renderHookScopes = $livewire?->getRenderHookScopes();
    $maxContentWidth ??= (filament()->getSimplePageMaxContentWidth() ?? Width::Large);

    if (is_string($maxContentWidth)) {
        $maxContentWidth = Width::tryFrom($maxContentWidth) ?? $maxContentWidth;
    }

    $isLogin = !filament()->auth()->check();
    $loginIllustration = \App\Models\SystemSetting::getLoginIllustrationUrl();
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @if ($isLogin)
        <div class="fi-login-split-wrapper">
            <div class="fi-login-left-panel" style="padding:0;overflow:hidden;background:#EAF2FF;display:flex;flex-direction:column;">
                <div style="padding:40px 40px 24px;">
                    <img src="/assets/posretail/01-logo-posretail.png" alt="POS Retail" style="height:40px;width:auto;margin-bottom:20px;">
                    <h2 style="font-size:26px;font-weight:800;letter-spacing:-0.02em;color:#17345C;line-height:1.25;margin:0 0 8px;">Kelola Toko Lebih Mudah,<br>Lebih Cepat, Lebih Untung</h2>
                    <p style="font-size:14px;color:#607895;margin:0 0 20px;">Satu sistem untuk penjualan, stok, pembelian, pelanggan, dan laporan.</p>
                    <ul style="list-style:none;margin:0;padding:0;display:grid;gap:10px;font-size:13px;color:#17345C;font-weight:600;">
                        <li><span style="display:inline-flex;width:22px;height:22px;border-radius:50%;background:#E8F8F0;color:#16A875;font-weight:800;align-items:center;justify-content:center;font-size:12px;margin-right:8px;">✓</span>Multi-outlet dalam satu dashboard</li>
                        <li><span style="display:inline-flex;width:22px;height:22px;border-radius:50%;background:#E8F8F0;color:#16A875;font-weight:800;align-items:center;justify-content:center;font-size:12px;margin-right:8px;">✓</span>Stok real-time + FEFO &amp; barcode</li>
                        <li><span style="display:inline-flex;width:22px;height:22px;border-radius:50%;background:#E8F8F0;color:#16A875;font-weight:800;align-items:center;justify-content:center;font-size:12px;margin-right:8px;">✓</span>Laporan keuangan &amp; laba rugi</li>
                        <li><span style="display:inline-flex;width:22px;height:22px;border-radius:50%;background:#E8F8F0;color:#16A875;font-weight:800;align-items:center;justify-content:center;font-size:12px;margin-right:8px;">✓</span>Web &amp; Android, online dan offline</li>
                    </ul>
                </div>
                <div style="padding:0 40px 40px;margin-top:auto;">
                    <img src="/assets/posretail/poster-login.png" alt="POS Retail — aplikasi kasir dan dashboard" style="width:100%;height:auto;max-height:46vh;object-fit:cover;object-position:center top;display:block;border-radius:14px;border:1px solid #E1EAF4;box-shadow:0 1px 3px rgba(28,63,104,0.05);">
                </div>
            </div>
            <div class="fi-login-right-panel">
                <div class="fi-login-right-inner">
                    <div class="fi-login-right-header">
                        <x-ti name="building-store" class="w-8 h-8 text-[#1769F5]" />
                        <span>Admin</span>
                    </div>
                    <div class="fi-login-form">
                        {{ $slot }}
                    </div>
                    @if(\App\Support\DemoMode::enabled())
                    <div class="fi-login-demo-box">
                        <strong>Demo Login</strong>
                        <div>Owner: owner@pos-retail.test / password</div>
                        <div>Manager: manager@pos-retail.test / password</div>
                        <div>Admin: admin@pos-retail.test / password</div>
                        <div>Kasir: kasir@pos-retail.test / password</div>
                        <div>Gudang: gudang@pos-retail.test / password</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        @props([
            'after' => null,
            'heading' => null,
            'subheading' => null,
        ])

        <div class="fi-simple-layout">
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, scopes: $renderHookScopes) }}

            @if (($hasTopbar ?? true) && filament()->auth()->check())
                <div class="fi-simple-layout-header">
                    @if (filament()->hasDatabaseNotifications())
                        @livewire(filament()->getDatabaseNotificationsLivewireComponent(), [
                            'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                            'position' => \Filament\Enums\DatabaseNotificationsPosition::Topbar,
                        ])
                    @endif

                    @if (filament()->hasUserMenu())
                        @livewire(Filament\Livewire\SimpleUserMenu::class)
                    @endif
                </div>
            @endif

            <div class="fi-simple-main-ctn">
                <main
                    @class([
                        'fi-simple-main',
                        ($maxContentWidth instanceof Width) ? "fi-width-{$maxContentWidth->value}" : $maxContentWidth,
                    ])
                >
                    {{ $slot }}
                </main>
            </div>

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
        </div>
    @endif
</x-filament-panels::layout.base>
