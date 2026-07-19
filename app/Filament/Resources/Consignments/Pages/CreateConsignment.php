<?php

namespace App\Filament\Resources\Consignments\Pages;

use App\Filament\Resources\Consignments\ConsignmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateConsignment extends CreateRecord
{
    protected static string $resource = ConsignmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $seq = \App\Models\Consignment::whereDate('created_at', today())->count() + 1;
        $data['consignment_number'] = 'CON-' . date('Ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
        $data['status'] = 'active';
        $data['sold_quantity'] = 0;

        return $data;
    }
}
