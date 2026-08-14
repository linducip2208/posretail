<?php

namespace App\Filament\Resources\Concerns;

use App\Models\SystemSetting;

trait RestaurantFeature
{
    public static function shouldRegisterNavigation(): bool
    {
        return SystemSetting::restaurantEnabled();
    }
}
