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
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
                'primary' => Color::hex('#0878F9'),
                'success' => Color::hex('#16A34A'),
                'warning' => Color::hex('#F59E0B'),
                'danger' => Color::hex('#DC2626'),
                'gray' => Color::hex('#64748B'),
            ])
            ->navigationGroups([
                NavigationGroup::make('Penjualan')->collapsed(false),
                NavigationGroup::make('Operasional')->collapsed(true),
                NavigationGroup::make('Inventory')->collapsed(false),
                NavigationGroup::make('Pembelian')->collapsed(true),
                NavigationGroup::make('Pelanggan')->collapsed(true),
                NavigationGroup::make('Promo')->collapsed(true),
                NavigationGroup::make('Laporan')->collapsed(false),
                NavigationGroup::make('Keuangan')->collapsed(true),
                NavigationGroup::make('Akuntansi')->collapsed(true),
                NavigationGroup::make('Sistem')->collapsed(true),
                NavigationGroup::make('Website')->collapsed(true),
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
                    ->icon('tabler-shopping-cart')
                    ->openUrlInNewTab()
                    ->sort(100)
                    ->visible(fn (): bool => auth()->check() && auth()->user()?->hasPermission('pos-access')),
            ]);
    }
}
