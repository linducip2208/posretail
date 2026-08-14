<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SystemSetting;
use App\Models\TaxInvoice;

class TaxInvoiceService
{
    /**
     * Generate e-Faktur (TaxInvoice) dari order yang selesai.
     */
    public static function generateFromOrder(Order $order, array $data = []): TaxInvoice
    {
        $taxPercent = (float) SystemSetting::getValue('tax_percent', '11');

        $dpp = (float) $order->subtotal - (float) $order->discount_amount;
        $ppnAmount = (float) $order->tax_amount > 0
            ? (float) $order->tax_amount
            : round($dpp * $taxPercent / 100, 2);

        return TaxInvoice::create([
            'invoice_number' => TaxInvoice::generateNumber(),
            'order_id' => $order->id,
            'outlet_id' => $order->outlet_id,
            'customer_npwp' => $data['customer_npwp'] ?? null,
            'customer_name' => $data['customer_name'] ?? $order->customer?->name ?? 'Konsumen Umum',
            'customer_address' => $data['customer_address'] ?? $order->customer?->address,
            'dpp' => $dpp,
            'ppn_amount' => $ppnAmount,
            'total_amount' => (float) $order->total_amount,
            'reference_number' => $order->order_number,
            'invoice_date' => now(),
            'status' => $data['status'] ?? 'issued',
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }
}
