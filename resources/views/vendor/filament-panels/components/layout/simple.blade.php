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
            <div class="fi-login-left-panel">
                <div class="fi-login-left-content">
                    <div class="fi-login-left-logo">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="36" height="36"><path d="M3 9l1.5-5h15L21 9v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9Z"/><path d="M3 9h18"/><path d="M9 22V11h6v11"/></svg>
                        <span>POS Retail</span>
                    </div>
                    <div class="fi-login-illustration">
                        @if($loginIllustration)
                            <img src="{{ $loginIllustration }}" alt="POS Illustration" style="max-width:100%;max-height:240px;object-fit:contain;">
                        @else
                            <svg viewBox="0 0 400 320" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <!-- Desk/Counter -->
                                <rect x="60" y="200" width="280" height="16" rx="4" fill="rgba(255,255,255,0.15)"/>
                                <rect x="80" y="216" width="240" height="80" rx="6" fill="rgba(255,255,255,0.08)"/>
                                <!-- POS Monitor -->
                                <rect x="180" y="120" width="120" height="78" rx="8" fill="rgba(255,255,255,0.12)"/>
                                <rect x="188" y="128" width="104" height="62" rx="4" fill="rgba(255,255,255,0.06)"/>
                                <rect x="230" y="68" width="20" height="54" rx="3" fill="rgba(255,255,255,0.1)"/>
                                <rect x="220" y="66" width="40" height="6" rx="3" fill="rgba(255,255,255,0.15)"/>
                                <!-- Monitor screen -->
                                <rect x="192" y="132" width="40" height="4" rx="2" fill="rgba(255,255,255,0.25)"/>
                                <rect x="192" y="140" width="60" height="3" rx="1.5" fill="rgba(255,255,255,0.15)"/>
                                <rect x="192" y="147" width="48" height="3" rx="1.5" fill="rgba(255,255,255,0.15)"/>
                                <!-- Receipt printer -->
                                <rect x="140" y="175" width="50" height="25" rx="4" fill="rgba(255,255,255,0.1)"/>
                                <rect x="150" y="180" width="30" height="3" rx="1.5" fill="rgba(255,255,255,0.15)"/>
                                <!-- Receipt paper -->
                                <path d="M155 175 Q165 140 160 100" stroke="rgba(255,255,255,0.5)" stroke-width="2" fill="none" stroke-linecap="round" stroke-dasharray="4 3"/>
                                <!-- Barcode scanner -->
                                <rect x="310" y="190" width="34" height="10" rx="3" fill="rgba(255,255,255,0.1)"/>
                                <rect x="314" y="192" width="12" height="6" rx="1" fill="rgba(255,255,255,0.15)"/>
                                <!-- Person - head -->
                                <circle cx="110" cy="105" r="20" fill="rgba(255,255,255,0.2)"/>
                                <!-- Person - body -->
                                <path d="M85 160 Q85 125 110 125 Q135 125 135 160" fill="rgba(255,255,255,0.15)"/>
                                <!-- Person - arms -->
                                <path d="M85 140 Q60 145 65 170" stroke="rgba(255,255,255,0.2)" stroke-width="8" stroke-linecap="round"/>
                                <path d="M135 140 Q160 135 155 165" stroke="rgba(255,255,255,0.2)" stroke-width="8" stroke-linecap="round"/>
                                <!-- Shopping bags -->
                                <rect x="250" y="195" width="20" height="18" rx="2" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.12)" stroke-width="1"/>
                                <path d="M254 195 Q260 188 266 195" stroke="rgba(255,255,255,0.15)" stroke-width="1" fill="none"/>
                            </svg>
                        @endif
                    </div>
                    <div class="fi-login-left-text">
                        <h2>Kelola Bisnis Retail</h2>
                        <p>Point of Sale, Inventori, Laporan — semua dalam satu dashboard.</p>
                    </div>
                </div>
            </div>
            <div class="fi-login-right-panel">
                <div class="fi-login-right-inner">
                    <div class="fi-login-right-header">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="32" height="32"><path d="M3 9l1.5-5h15L21 9v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9Z"/><path d="M3 9h18"/><path d="M9 22V11h6v11"/></svg>
                        <span>Admin</span>
                    </div>
                    <div class="fi-login-form">
                        {{ $slot }}
                    </div>
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
