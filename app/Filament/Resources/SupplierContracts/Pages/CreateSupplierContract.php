<?php

namespace App\Filament\Resources\SupplierContracts\Pages;

use App\Filament\Resources\SupplierContracts\SupplierContractResource;
use App\Models\SupplierContract;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

class CreateSupplierContract extends CreateRecord
{
    protected static string $resource = SupplierContractResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['contract_number'] = $this->generateContractNumber();

        return $data;
    }

    protected function generateContractNumber(): string
    {
        $prefix = 'CNT-' . Carbon::now()->format('Ymd') . '-';
        $last = SupplierContract::where('contract_number', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(contract_number) DESC, contract_number DESC')
            ->first();
        $num = $last ? (int) substr($last->contract_number, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
