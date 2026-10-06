<?php

namespace App\Filament\Resources\SupplierRatings\Pages;

use App\Filament\Resources\SupplierRatings\SupplierRatingResource;
use App\Models\PurchaseOrder;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditSupplierRating extends EditRecord
{
    protected static string $resource = SupplierRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $poId = (int) ($data['purchase_order_id'] ?? $this->record->purchase_order_id);
        $user = auth()->user();
        $po = PurchaseOrder::find($poId);

        if (! $po) {
            throw ValidationException::withMessages([
                'purchase_order_id' => 'Purchase order tidak ditemukan.',
            ]);
        }

        if ($user && ! $user->hasPermission('*')
            && ! in_array((int) $po->outlet_id, $user->getAccessibleOutletIds(), true)) {
            throw ValidationException::withMessages([
                'purchase_order_id' => 'Anda tidak memiliki akses ke outlet purchase order ini.',
            ]);
        }

        return $data;
    }
}
