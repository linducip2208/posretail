<?php

namespace App\Filament\Resources\Concerns;

trait AuthorizesByNavigation
{
    public static function canViewAny(): bool
    {
        return static::authorizedFor('view');
    }

    public static function canCreate(): bool
    {
        return static::authorizedFor('create');
    }

    public static function canEdit($record): bool
    {
        return static::authorizedFor('edit');
    }

    public static function canDelete($record): bool
    {
        return static::authorizedFor('delete');
    }

    public static function canDeleteAny(): bool
    {
        return static::authorizedFor('delete');
    }

    protected static function authorizedFor(string $action): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->hasPermission('*')) {
            return true;
        }

        $group = static::permissionGroup();
        return $user->hasPermission("{$action}-{$group}");
    }

    protected static function permissionGroup(): string
    {
        $navigationGroup = (string) static::getNavigationGroup();
        $groups = [
            'Penjualan' => 'transaksi',
            'Inventory' => 'inventori',
            'Pembelian' => 'pembelian',
            'Customer' => 'master-data',
            'Supplier' => 'master-data',
            'Outlet' => 'master-data',
            'Keuangan' => 'finance',
            'Akuntansi' => 'finance',
            'Promo' => 'loyalitas',
            'Laporan' => 'laporan',
            'Pegawai' => 'sistem',
            'Notifikasi' => 'sistem',
            'Integrasi' => 'integrasi',
            'Pengaturan' => 'sistem',
            'Sistem' => 'sistem',
            'Website' => 'marketing',
        ];

        foreach ($groups as $label => $permissionGroup) {
            if (str_contains($navigationGroup, $label)) {
                return $permissionGroup;
            }
        }

        return 'sistem';
    }
}
