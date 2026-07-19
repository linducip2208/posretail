<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketplaceWebhookController extends Controller
{
    public function handle(Request $request, string $platform): JsonResponse
    {
        if (!in_array($platform, ['tokopedia', 'shopee', 'lazada'])) {
            return response()->json(['message' => 'Platform tidak didukung'], 400);
        }

        $mapping = $this->getMapping($platform);

        $order = MarketplaceOrder::create([
            'platform' => $platform,
            'platform_order_id' => $request->input($mapping['order_id']),
            'platform_invoice' => $request->input($mapping['invoice']),
            'customer_name' => $request->input($mapping['customer_name']),
            'customer_phone' => $request->input($mapping['customer_phone']),
            'shipping_address' => $request->input($mapping['address']),
            'total_amount' => (float) ($request->input($mapping['total']) ?? 0),
            'shipping_fee' => (float) ($request->input($mapping['shipping_fee']) ?? 0),
            'status' => 'new',
            'raw_payload' => $request->all(),
            'items' => $request->input($mapping['items']),
        ]);

        return response()->json([
            'success' => true,
            'id' => $order->id,
            'platform_order_id' => $order->platform_order_id,
        ], 201);
    }

    private function getMapping(string $platform): array
    {
        $mappings = [
            'tokopedia' => [
                'order_id' => 'order_id',
                'invoice' => 'invoice_ref',
                'customer_name' => 'buyer.name',
                'customer_phone' => 'buyer.phone',
                'address' => 'shipping.address.full',
                'total' => 'total_amount',
                'shipping_fee' => 'shipping_fee',
                'items' => 'products',
            ],
            'shopee' => [
                'order_id' => 'ordersn',
                'invoice' => 'invoice_no',
                'customer_name' => 'recipient.name',
                'customer_phone' => 'recipient.phone',
                'address' => 'recipient.full_address',
                'total' => 'total_amount',
                'shipping_fee' => 'estimate_shipping_fee',
                'items' => 'item_list',
            ],
            'lazada' => [
                'order_id' => 'order_number',
                'invoice' => 'invoice_number',
                'customer_name' => 'customer.first_name',
                'customer_phone' => 'customer.phone',
                'address' => 'address.shipping.full_address',
                'total' => 'price',
                'shipping_fee' => 'shipping_fee',
                'items' => 'order_items',
            ],
        ];

        return $mappings[$platform];
    }
}
