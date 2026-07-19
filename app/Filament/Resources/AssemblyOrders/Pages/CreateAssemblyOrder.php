<?php

namespace App\Filament\Resources\AssemblyOrders\Pages;

use App\Filament\Resources\AssemblyOrders\AssemblyOrderResource;
use App\Models\AssemblyOrder;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateAssemblyOrder extends CreateRecord
{
    protected static string $resource = AssemblyOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['assembly_number'] = AssemblyOrder::generateAssemblyNumber();
        $data['user_id'] = $data['user_id'] ?? auth()->id();
        $data['status'] = 'draft';

        return $data;
    }
}
