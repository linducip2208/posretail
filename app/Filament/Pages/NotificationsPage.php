<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class NotificationsPage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = '🔔 Notifikasi';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $title = 'Notifikasi';

    protected static ?string $navigationLabel = 'Notifikasi';

    protected string $view = 'filament.pages.notifications';

    public function getNotificationsProperty()
    {
        return auth()->user()->notifications()->latest()->paginate(20);
    }

    public function getUnreadCountProperty(): int
    {
        return auth()->user()->unreadNotifications()->count();
    }

    public function getTotalCountProperty(): int
    {
        return auth()->user()->notifications()->count();
    }

    public function markAsRead(string $id): void
    {
        auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
    }
}
