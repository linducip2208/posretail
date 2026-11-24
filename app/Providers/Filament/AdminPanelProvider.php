<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Models\SystemSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->brandName(fn () => SystemSetting::getAppName())
            ->brandLogo(fn () => new HtmlString('<img src="'.(SystemSetting::getLogoUrl() ?: '/assets/posretail/01-logo-posretail.png').'" alt="POS Retail" style="height:2rem">'))
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop(true)
            ->sidebarWidth('15rem')
            ->collapsedSidebarWidth('4.5rem')
            ->databaseNotifications(true)
            ->favicon('/assets/posretail/02-icon-android.png')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::hex('#4F46E5'),
                'success' => Color::hex('#16A34A'),
                'warning' => Color::hex('#F59E0B'),
                'danger' => Color::hex('#DC2626'),
                'gray' => Color::hex('#64748B'),
            ])
            ->navigationGroups([
                NavigationGroup::make('POS')->collapsed(false),
                NavigationGroup::make('Sales')->collapsed(false),
                NavigationGroup::make('Catalog')->collapsed(false),
                NavigationGroup::make('Purchasing')->collapsed(false),
                NavigationGroup::make('People')->collapsed(false),
                NavigationGroup::make('Reports')->collapsed(false),
                NavigationGroup::make('Marketing')->collapsed(false),
                NavigationGroup::make('Admin')->collapsed(false),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\RedirectAdminHomeToApp::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationItems([
                NavigationItem::make('POS Kasir')
                    ->url('/pos')
                    ->icon('tabler-shopping-cart')
                    ->group('POS')
                    ->openUrlInNewTab()
                    ->sort(1)
                    ->visible(fn (): bool => auth()->check() && auth()->user()?->hasPermission('pos-access')),
            ])
            ->renderHook(
                PanelsRenderHook::SCRIPTS_AFTER,
                fn () => new HtmlString(
                    <<<'HTML'
                    <script>
                    (function () {
                        // Workaround bug Filament: collapsedGroups default null
                        // (localStorage kosong) -> groupIsCollapsed() throw
                        // "Cannot read properties of null" dan sidebar mati.
                        // Reset sekali: browser lama menyimpan grup terlipat versi lama,
                        // sehingga menu terlihat sedikit meski server sudah expand semua.
                        function fixSidebarCollapsedGroups() {
                            try {
                                var s = window.Alpine && window.Alpine.store('sidebar');
                                if (!s) return;
                                if (s.collapsedGroups == null) {
                                    s.collapsedGroups = [];
                                }
                                try {
                                    if (localStorage.getItem('posretail:sidebar:v') !== '2') {
                                        s.collapsedGroups = [];
                                        localStorage.removeItem('filament:sidebar:collapsed_groups');
                                        localStorage.setItem('posretail:sidebar:v', '2');
                                    }
                                } catch (e) {}
                            } catch (e) {}
                        }
                        document.addEventListener('alpine:initialized', fixSidebarCollapsedGroups);
                        document.addEventListener('livewire:navigated', fixSidebarCollapsedGroups);
                        if (document.readyState === 'complete') {
                            fixSidebarCollapsedGroups();
                        } else {
                            window.addEventListener('load', fixSidebarCollapsedGroups);
                        }
                    })();
                    </script>
                    HTML
                ),
            );
    }
}
