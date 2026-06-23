<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class ApprovalService
{
    public function getThreshold(): float
    {
        $value = SystemSetting::where('key', 'approval_threshold')->value('value');

        return $value ? (float) $value : 5000000;
    }

    public function needsApproval(Order $order): bool
    {
        return $order->total_amount >= $this->getThreshold()
            && $order->order_status !== 'pending_approval'
            && $order->order_status === 'completed';
    }

    public function flagForApproval(Order $order): void
    {
        $order->update(['order_status' => 'pending_approval']);

        Log::info('Order flagged for approval', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'total_amount' => $order->total_amount,
        ]);
    }

    public function approve(Order $order, int $approvedBy, ?string $notes = null): void
    {
        $order->update([
            'order_status' => 'completed',
            'notes' => ($order->notes ? $order->notes . "\n" : '') . "Approved by user #{$approvedBy}" . ($notes ? ": {$notes}" : ''),
        ]);

        Log::info('Order approved', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'approved_by' => $approvedBy,
        ]);
    }

    public function reject(Order $order, int $rejectedBy, string $reason): void
    {
        $order->update([
            'order_status' => 'cancelled',
            'notes' => ($order->notes ? $order->notes . "\n" : '') . "Rejected by user #{$rejectedBy}: {$reason}",
        ]);

        Log::info('Order rejected', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'rejected_by' => $rejectedBy,
            'reason' => $reason,
        ]);
    }

    public function getPendingApprovals(): array
    {
        $orders = Order::where('order_status', 'pending_approval')
            ->with(['user', 'outlet', 'customer'])
            ->latest()
            ->get();

        $purchaseOrders = PurchaseOrder::where('status', 'draft')
            ->where('total_amount', '>=', $this->getThreshold())
            ->with(['user', 'supplier'])
            ->latest()
            ->get();

        return [
            'orders' => $orders,
            'purchase_orders' => $purchaseOrders,
            'opname' => StockOpname::where('status', 'pending')
                ->with(['user', 'outlet'])
                ->latest()
                ->get(),
            'transfers' => StockTransfer::where('status', 'draft')
                ->with(['user', 'fromOutlet', 'toOutlet'])
                ->latest()
                ->get(),
            'threshold' => $this->getThreshold(),
        ];
    }
}
