<?php

namespace App\Filament\Resources\HeldCarts\Pages;

use App\Filament\Resources\HeldCarts\HeldCartResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHeldCart extends EditRecord
{
    protected static string $resource = HeldCartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
