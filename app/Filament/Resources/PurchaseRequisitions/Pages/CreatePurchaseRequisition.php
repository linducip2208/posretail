<?php

namespace App\Filament\Resources\PurchaseRequisitions\Pages;

use App\Filament\Resources\PurchaseRequisitions\PurchaseRequisitionResource;
use App\Models\PurchaseRequisition;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseRequisition extends CreateRecord
{
    protected static string $resource = PurchaseRequisitionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = auth()->id();
        $data['status'] = 'draft';
        $data['pr_number'] = PurchaseRequisition::generateNumber();

        return $data;
    }
}
