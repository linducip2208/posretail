<?php

namespace App\Filament\Resources\KitchenTickets\Pages;

use App\Filament\Resources\KitchenTickets\KitchenTicketResource;
use Filament\Resources\Pages\ListRecords;

class ListKitchenTickets extends ListRecords
{
    protected static string $resource = KitchenTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
