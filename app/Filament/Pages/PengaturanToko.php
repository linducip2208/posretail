<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class PengaturanToko extends Page
{
    protected static string|UnitEnum|null $navigationGroup = '⚙️ Pengaturan';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $title = 'Pengaturan Toko';

    protected string $view = 'filament.pages.pengaturan-toko';

    public bool $restaurantEnabled = true;

    public function mount(): void
    {
        $this->restaurantEnabled = SystemSetting::restaurantEnabled();
    }

    public function save(): void
    {
        SystemSetting::setValue('restaurant_enabled', $this->restaurantEnabled ? '1' : '0');

        Notification::make()
            ->title('Pengaturan disimpan')
            ->body('Mode restoran telah diperbarui. Muat ulang halaman untuk melihat perubahan menu.')
            ->success()
            ->send();
    }
}
