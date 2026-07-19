<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Filament\Resources\Assets\CompanyAssetResource;
use App\Models\CompanyAsset;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

class CreateAsset extends CreateRecord
{
    protected static string $resource = CompanyAssetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['asset_code'] = $this->generateAssetCode();
        $data['current_value'] = $data['purchase_value'];
        $data['monthly_depreciation'] = $data['useful_life_months'] > 0
            ? round(($data['purchase_value'] - ($data['salvage_value'] ?? 0)) / $data['useful_life_months'], 2)
            : 0;

        return $data;
    }

    protected function generateAssetCode(): string
    {
        $prefix = 'AST-' . Carbon::now()->format('Ymd') . '-';
        $last = CompanyAsset::where('asset_code', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(asset_code) DESC, asset_code DESC')
            ->first();
        $num = $last ? (int) substr($last->asset_code, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $record->update([
            'current_value' => $record->purchase_value,
            'monthly_depreciation' => $record->useful_life_months > 0
                ? round(($record->purchase_value - $record->salvage_value) / $record->useful_life_months, 2)
                : 0,
        ]);
    }
}
