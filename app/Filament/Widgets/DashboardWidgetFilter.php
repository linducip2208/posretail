<?php

namespace App\Filament\Widgets;

trait DashboardWidgetFilter
{
    public static function canView(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        return static::isVisibleToRole($user->role)
            && static::isVisibleToUser($user);
    }

    protected static function isVisibleToRole(?string $role): bool
    {
        return true;
    }

    protected static function isVisibleToUser($user): bool
    {
        return true;
    }
}
