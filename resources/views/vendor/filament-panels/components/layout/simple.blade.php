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
            <div class="fi-login-left-panel" style="padding:0;overflow:hidden;">
                <img src="/assets/posretail/poster-login.png" alt="POS Retail — Kelola Toko Lebih Mudah, Lebih Cepat, Lebih Untung" style="width:100%;height:100%;object-fit:cover;object-position:center top;display:block;min-height:100vh;">
            </div>
            <div class="fi-login-right-panel">
                <div class="fi-login-right-inner">
                    <div class="fi-login-right-header">
                        <x-ti name="building-store" class="w-8 h-8 text-[#206bc4]" />
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
