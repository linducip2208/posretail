<?php

namespace App\Filament\Resources\HeldCarts\Pages;

use App\Filament\Resources\HeldCarts\HeldCartResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHeldCarts extends ListRecords
{
    protected static string $resource = HeldCartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
