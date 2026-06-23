<?php

namespace App\Filament\Resources\KitchenTickets\Pages;

use App\Filament\Resources\KitchenTickets\KitchenTicketResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKitchenTicket extends EditRecord
{
    protected static string $resource = KitchenTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
