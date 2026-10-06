<?php

namespace App\Filament\Resources\SupplierRatings\Pages;

use App\Filament\Resources\SupplierRatings\SupplierRatingResource;
use App\Models\PurchaseOrder;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateSupplierRating extends CreateRecord
{
    protected static string $resource = SupplierRatingResource::class;

    /**
     * Tolak supplier_id(outlet A) + purchase_order_id(outlet B):
     * PO harus berada di outlet yang boleh diakses user.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->assertPurchaseOrderAccess((int) ($data['purchase_order_id'] ?? 0));

        return $data;
    }

    protected function assertPurchaseOrderAccess(int $poId): void
    {
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
    }
}
