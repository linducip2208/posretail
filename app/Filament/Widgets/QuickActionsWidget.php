<?php

namespace App\Filament\Widgets;

use App\Support\TablerIcons;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected string $view = 'filament.widgets.quick-actions';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 7;

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'admin', 'kasir']);
    }

    public function getViewData(): array
    {
        // URL statis (bukan getUrl()) dan SVG pre-render agar widget
        // selalu ter-render, termasuk saat hydrasi Livewire susulan.
        $make = fn (string $label, string $icon, string $url, bool $blank = false, bool $primary = false): array => [
            'label' => $label,
            'iconSvg' => TablerIcons::svg($icon, 'h-4 w-4'),
            'url' => $url,
            'blank' => $blank,
            'primary' => $primary,
        ];

        return [
            'actions' => [
                $make('Penjualan Baru', 'shopping-cart', '/pos', true, true),
                $make('Tambah Produk', 'plus', '/admin/products/create'),
                $make('Stock Opname', 'clipboard-check', '/admin/stock-opnames'),
                $make('Purchase Order', 'truck', '/admin/purchase-orders'),
                $make('Customer', 'users', '/admin/customers'),
                $make('Laporan', 'report', '/admin/laporan-penjualan'),
            ],
        ];
    }
}
