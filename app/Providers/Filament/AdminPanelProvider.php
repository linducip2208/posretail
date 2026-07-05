<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationItem;
use App\Models\SystemSetting;
use Illuminate\Support\HtmlString;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(fn () => SystemSetting::getAppName())
            ->brandLogo(fn () => SystemSetting::getLogoUrl() ? new HtmlString('<img src="' . SystemSetting::getLogoUrl() . '" alt="Logo" style="height:2rem">') : null)
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop(true)
            ->sidebarWidth('16rem')
            ->collapsedSidebarWidth('4.5rem')
            ->databaseNotifications(true)
            ->favicon('/favicon.svg')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->navigationGroups([
                '🏪 Master Data',
                '💰 Transaksi',
                '📦 Pembelian',
                '💵 Finance',
                '📋 Inventori',
                '🔄 Operasional',
                '⭐ Loyalitas',
                '📊 Laporan',
                '📰 Marketing',
                '🔗 Integrasi',
                '⚙️ Sistem',
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationItems([
                NavigationItem::make('POS Kasir')
                    ->url('/pos')
                    ->icon('heroicon-o-shopping-cart')
                    ->openUrlInNewTab()
                    ->sort(100)
                    ->visible(fn (): bool => auth()->check() && auth()->user()?->hasPermission('pos-access')),
            ]);
    }
}
