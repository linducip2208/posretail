<?php

namespace App\Filament\Resources\TaxInvoices\Pages;

use App\Filament\Resources\TaxInvoices\TaxInvoiceResource;
use App\Models\Order;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTaxInvoice extends CreateRecord
{
    protected static string $resource = TaxInvoiceResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $order = Order::find($data['order_id']);
        $dpp = round($order->total_amount * 100 / 111, 2);
        $ppn = round($dpp * 0.11, 2);

        $data['dpp'] = $dpp;
        $data['ppn_amount'] = $ppn;
        $data['total_amount'] = $order->total_amount;
        $data['outlet_id'] = $order->outlet_id;
        $data['status'] = 'draft';
        $data['created_by'] = auth()->id();
        $data['invoice_number'] = static::generateInvoiceNumber();

        return static::getModel()::create($data);
    }

    protected static function generateInvoiceNumber(): string
    {
        $prefix = 'FP-' . now()->format('Ymd');
        $last = \App\Models\TaxInvoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();
        $seq = $last ? ((int) substr($last->invoice_number, -4)) + 1 : 1;
        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
