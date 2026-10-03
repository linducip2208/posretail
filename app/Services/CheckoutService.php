<?php

namespace App\Services;

use App\Models\GiftCard;
use App\Models\GiftCardUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SerialNumber;
use App\Models\StockMovement;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * Payload ternormalisasi:
     * [
     *   'outlet_id' => int (required),
     *   'items' => [['product_id'=>int,'product_variant_id'=>?int,'quantity'=>int,'discount_percent'=>float]],
     *   'payments' => [['payment_method_id'=>int,'amount'=>float]],
     *   'customer_id'=>?int,'employee_id'=>?int,
     *   'order_type'=>?string,'order_notes'=>?string,'notes'=>?string,
     *   'voucher_code'=>?string,'use_tax'=>bool,'deposit_amount'=>float,
     *   'is_installment'=>bool,'installment_period'=>?string,'installment_count'=>int,
     *   'serial_numbers'=> [product_id => [sn,...]] (opsional, atau per-item 'serial_numbers'=>[]),
     * ]
     */
    public function checkout(array $payload, int $userId): Order
    {
        return DB::transaction(function () use ($payload, $userId) {
            $outletId = (int) ($payload['outlet_id'] ?? 0);
            if ($outletId <= 0) {
                throw ValidationException::withMessages(['outlet_id' => 'Outlet wajib diisi.']);
            }
            if (empty($payload['items']) || ! is_array($payload['items'])) {
                throw ValidationException::withMessages(['items' => 'Keranjang masih kosong.']);
            }
            // payments boleh kosong → order pending. Jika diisi harus array.
            $paymentsInput = $payload['payments'] ?? [];
            if (! is_array($paymentsInput)) {
                throw ValidationException::withMessages(['payments' => 'Pembayaran tidak valid.']);
            }

            $useTax = (bool) ($payload['use_tax'] ?? true);
            $taxPercent = $useTax ? (float) SystemSetting::getValue('tax_percent', '0') : 0.0;

            // 1. Kunci + validasi stok + ambil harga server
            $lines = [];
            $subtotal = 0.0;
            $discountTotal = 0.0;

            foreach ($payload['items'] as $idx => $raw) {
                $productId = (int) ($raw['product_id'] ?? 0);
                $variantId = isset($raw['product_variant_id']) && $raw['product_variant_id'] ? (int) $raw['product_variant_id'] : null;
                $qty = (int) ($raw['quantity'] ?? 0);
                $discPct = (float) ($raw['discount_percent'] ?? 0);

                if ($productId <= 0 || $qty < 1) {
                    throw ValidationException::withMessages(["items.$idx.quantity" => 'Item tidak valid.']);
                }
                if ($discPct < 0 || $discPct > 100) {
                    throw ValidationException::withMessages(["items.$idx.discount_percent" => 'Diskon 0-100%.']);
                }

                /** @var Product $product */
                $product = Product::where('id', $productId)->lockForUpdate()->first();
                if (! $product || ! $product->active) {
                    throw ValidationException::withMessages(["items.$idx.product_id" => 'Produk tidak tersedia.']);
                }

                $variant = null;
                if ($variantId) {
                    $variant = ProductVariant::where('id', $variantId)
                        ->where('product_id', $productId)
                        ->lockForUpdate()->first();
                    if (! $variant) {
                        throw ValidationException::withMessages(["items.$idx.product_variant_id" => 'Varian tidak valid.']);
                    }
                    if ((int) $variant->current_stock < $qty) {
                        throw ValidationException::withMessages(["items.$idx.quantity" => "Stok varian \"{$product->name} ({$variant->name})\" kurang (sisa {$variant->current_stock})."]);
                    }
                }

                // Stok induk selalu dicek (konsisten dengan PosController)
                $product->refresh();
                if ((int) $product->current_stock < $qty) {
                    throw ValidationException::withMessages(["items.$idx.quantity" => "Stok \"{$product->name}\" kurang (sisa {$product->current_stock})."]);
                }

                // Harga server — abaikan harga dari client (anti price-tampering)
                $unitPrice = $variant && (float) $variant->selling_price > 0
                    ? (float) $variant->selling_price
                    : (float) $product->selling_price;

                $lineSubtotal = $unitPrice * $qty;
                $lineDisc = round($lineSubtotal * $discPct / 100, 2);

                $subtotal += $lineSubtotal;
                $discountTotal += $lineDisc;

                // Validasi serial/IMEI
                $serials = $raw['serial_numbers'] ?? [];
                $this->assertSerials($product, $qty, is_array($serials) ? $serials : []);

                $lines[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discPct,
                    'discount_amount' => $lineDisc,
                    'subtotal' => $lineSubtotal - $lineDisc,
                    'serial_numbers' => is_array($serials) ? array_values($serials) : [],
                ];
            }

            // 2. Promo otomatis (TERBAIK, tidak di-stack) + voucher (stack, cap subtotal)
            $promoLines = array_map(fn ($l) => [
                'product_id' => $l['product_id'],
                'quantity' => $l['quantity'],
                'unit_price' => $l['unit_price'],
                'subtotal' => $l['subtotal'],
            ], $lines);
            $promoDiscount = 0.0;
            $promoName = null;
            if (! ($payload['skip_promo'] ?? false)) {
                $best = (new PromoService)->bestDiscount($outletId, $subtotal - $discountTotal, $promoLines);
                $promoDiscount = (float) $best['discount'];
                $promoName = $best['promo']?->name;
            }

            $voucherDiscount = 0.0;
            $voucher = null;
            if (! empty($payload['voucher_code'])) {
                $resolved = $this->resolveVoucher((string) $payload['voucher_code'], $subtotal - $discountTotal - $promoDiscount);
                $voucher = $resolved['giftCard'];
                $voucherDiscount = $resolved['discount'];
            }

            $totalDiscount = min($subtotal, $discountTotal + $promoDiscount + $voucherDiscount);
            $taxable = max(0, $subtotal - $totalDiscount);
            $taxAmount = round($taxable * $taxPercent / 100, 2);
            $totalAmount = round($taxable + $taxAmount, 2);

            $payments = collect($paymentsInput)->map(fn ($p) => [
                'payment_method_id' => (int) ($p['payment_method_id'] ?? 0),
                'amount' => (float) ($p['amount'] ?? 0),
            ])->values();
            foreach ($payments as $p) {
                if ($p['payment_method_id'] <= 0 || $p['amount'] < 0) {
                    throw ValidationException::withMessages(['payments' => 'Pembayaran tidak valid.']);
                }
            }
            $totalPaid = (float) $payments->sum('amount');
            $deposit = (float) ($payload['deposit_amount'] ?? 0);
            $isPending = $payments->isEmpty() && $deposit <= 0; // QR self-order tanpa bayar

            // 3. Nomor antrian aman dalam transaksi (lock baris hari ini)
            $todayCount = Order::where('outlet_id', $outletId)
                ->whereDate('created_at', today())
                ->lockForUpdate()
                ->count();
            $queueNumber = str_pad($todayCount + 1, 3, '0', STR_PAD_LEFT);

            // 4. Nomor order unik (retry anti-collision)
            $orderNumber = $this->uniqueOrderNumber();

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $payload['customer_id'] ?? null,
                'outlet_id' => $outletId,
                'user_id' => $userId,
                'employee_id' => $payload['employee_id'] ?? null,
                'order_type' => $payload['order_type'] ?? SystemSetting::getDefaultOrderType(),
                'queue_number' => $queueNumber,
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'deposit_amount' => $deposit,
                'remaining_amount' => max(0, $totalAmount - $totalPaid - $deposit),
                'is_installment' => (bool) ($payload['is_installment'] ?? false),
                'installment_period' => $payload['installment_period'] ?? null,
                'installment_count' => (int) ($payload['installment_count'] ?? 1),
                'payment_status' => $isPending ? 'pending' : ((($totalPaid + $deposit) >= $totalAmount ? 'paid' : 'partial')),
                'order_status' => $isPending ? ($payload['order_status'] ?? 'pending') : 'completed',
                'order_notes' => $payload['order_notes'] ?? null,
                'notes' => trim(($promoName ? "[promo:$promoName] " : '').($payload['notes'] ?? '')),
            ]);

            // 5. Tulis item + kurangi stok + movement
            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];
                $variant = $line['variant'];

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product_id'],
                    'product_variant_id' => $line['product_variant_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount_percent' => $line['discount_percent'],
                    'discount_amount' => $line['discount_amount'],
                    'subtotal' => $line['subtotal'],
                    'serial_number' => implode(',', $line['serial_numbers']),
                ]);

                // Tandai serial terjual
                $warrantyMonths = (int) ($product->warranty_months ?? 0);
                foreach ($line['serial_numbers'] as $sn) {
                    SerialNumber::where('product_id', $line['product_id'])
                        ->where('serial_number', $sn)
                        ->where('status', 'in_stock')
                        ->update([
                            'status' => 'sold',
                            'order_item_id' => $orderItem->id,
                            'outlet_id' => $outletId,
                            'warranty_expires_at' => $warrantyMonths > 0
                                ? now()->addMonths($warrantyMonths)->toDateString()
                                : null,
                        ]);
                }

                // Kurangi varian (jika ada) DAN induk — konsisten, anti inflasi/deflasi
                if ($variant) {
                    $variant->decrement('current_stock', $line['quantity']);
                }
                $product->decrement('current_stock', $line['quantity']);

                StockMovement::create([
                    'product_id' => $line['product_id'],
                    'product_variant_id' => $line['product_variant_id'],
                    'outlet_id' => $outletId,
                    'type' => 'out',
                    'quantity' => $line['quantity'],
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'notes' => 'Penjualan #'.$order->order_number,
                ]);
            }

            // 6. Voucher usage
            if ($voucher) {
                GiftCardUsage::create([
                    'gift_card_id' => $voucher->id,
                    'order_id' => $order->id,
                    'amount_used' => $voucherDiscount,
                ]);
                if ($voucher->type === 'nominal') {
                    $voucher->decrement('remaining_balance', $voucherDiscount);
                }
                $voucher->increment('used_count');
                if ($voucher->used_count >= $voucher->max_usage) {
                    $voucher->update(['status' => 'used']);
                }
            }

            // 7. Payments
            foreach ($payments as $idx => $p) {
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method_id' => $p['payment_method_id'],
                    'amount' => $p['amount'],
                    'split_index' => $idx,
                    'status' => 'success',
                    'paid_at' => now(),
                ]);
            }

            return $order->fresh();
        });
    }

    /**
     * Split order: pindahkan sebagian item ke order baru (split bill).
     * $moves = [['order_item_id'=>int,'quantity'=>int], ...]
     */
    public function splitBill(Order $order, array $moves, int $userId, array $payments = []): Order
    {
        return DB::transaction(function () use ($order, $moves, $userId, $payments) {
            $order->loadMissing('orderItems');
            $moveMap = collect($moves)->keyBy('order_item_id');

            if ($moveMap->isEmpty()) {
                throw ValidationException::withMessages(['splits' => 'Item split wajib diisi.']);
            }

            $newSubtotal = 0;
            foreach ($order->orderItems as $item) {
                $move = $moveMap->get($item->id);
                if (! $move) {
                    continue;
                }
                $qty = (int) ($move['quantity'] ?? 0);
                if ($qty < 1 || $qty > $item->quantity) {
                    throw ValidationException::withMessages(['splits' => "Qty split item #{$item->id} tidak valid."]);
                }
                $ratio = $qty / $item->quantity;
                $newSubtotal += ($item->subtotal * $ratio);
            }

            if ($newSubtotal <= 0) {
                throw ValidationException::withMessages(['splits' => 'Total split harus > 0.']);
            }

            $child = Order::create([
                'order_number' => $this->uniqueOrderNumber(),
                'customer_id' => $order->customer_id,
                'outlet_id' => $order->outlet_id,
                'user_id' => $userId,
                'order_type' => $order->order_type,
                'queue_number' => $order->queue_number.'-S',
                'subtotal' => $newSubtotal,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $newSubtotal,
                'deposit_amount' => 0,
                'remaining_amount' => $newSubtotal,
                'payment_status' => 'pending',
                'order_status' => 'completed',
                'notes' => 'Split dari #'.$order->order_number,
            ]);

            foreach ($order->orderItems as $item) {
                $move = $moveMap->get($item->id);
                if (! $move) {
                    continue;
                }
                $qty = (int) $move['quantity'];
                $ratio = $qty / $item->quantity;

                OrderItem::create([
                    'order_id' => $child->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $qty,
                    'unit_price' => $item->unit_price,
                    'discount_percent' => $item->discount_percent,
                    'discount_amount' => round($item->discount_amount * $ratio, 2),
                    'subtotal' => round($item->subtotal * $ratio, 2),
                ]);
            }

            $paid = 0;
            foreach ($payments as $idx => $p) {
                Payment::create([
                    'order_id' => $child->id,
                    'payment_method_id' => (int) $p['payment_method_id'],
                    'amount' => (float) $p['amount'],
                    'split_index' => $idx,
                    'status' => 'success',
                    'paid_at' => now(),
                ]);
                $paid += (float) $p['amount'];
            }
            $child->update([
                'payment_status' => $paid >= $newSubtotal ? 'paid' : 'partial',
                'remaining_amount' => max(0, $newSubtotal - $paid),
            ]);

            return $child->fresh();
        });
    }

    protected function uniqueOrderNumber(): string
    {
        do {
            $number = 'ORD-'.date('Ymd-His').'-'.strtoupper(Str::random(4));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    protected function assertSerials(Product $product, int $qty, array $serials): void
    {
        $tracking = $product->serial_tracking ?? 'none';
        if ($tracking === 'none') {
            return;
        }
        if ($tracking === 'required' && count($serials) !== $qty) {
            throw ValidationException::withMessages([
                'items' => "IMEI wajib lengkap untuk \"{$product->name}\" (jumlah {$qty}).",
            ]);
        }
        if ($tracking === 'optional' && count($serials) > 0 && count($serials) !== $qty) {
            throw ValidationException::withMessages([
                'items' => "Jumlah IMEI \"{$product->name}\" harus sama dengan qty ({$qty}).",
            ]);
        }
        foreach ($serials as $sn) {
            $exists = SerialNumber::where('product_id', $product->id)
                ->where('serial_number', $sn)
                ->where('status', 'in_stock')
                ->exists();
            if (! $exists) {
                throw ValidationException::withMessages([
                    'items' => "IMEI \"{$sn}\" tidak tersedia.",
                ]);
            }
        }
    }

    protected function resolveVoucher(string $code, float $subtotal): array
    {
        $giftCard = GiftCard::where('code', trim($code))->first();
        if (! $giftCard || ! $giftCard->isValid()) {
            throw ValidationException::withMessages(['voucher_code' => 'Voucher tidak valid/kadaluarsa.']);
        }
        if ($subtotal < (float) $giftCard->min_purchase) {
            throw ValidationException::withMessages(['voucher_code' => 'Minimal pembelian Rp '.number_format((float) $giftCard->min_purchase, 0, ',', '.')]);
        }
        $discount = $giftCard->type === 'discount_percent'
            ? round($subtotal * (float) $giftCard->value / 100, 2)
            : min((float) $giftCard->remaining_balance, $subtotal);

        return ['giftCard' => $giftCard, 'discount' => $discount];
    }
}
