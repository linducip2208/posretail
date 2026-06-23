<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class SendReminderNotifications extends Command
{
    protected $signature = 'pos:send-reminders';

    protected $description = 'Send reminder notifications for low stock and pending orders';

    public function handle(): int
    {
        $lowStockCount = Product::where('active', true)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->count();

        if ($lowStockCount > 0) {
            $this->info("{$lowStockCount} produk dengan stok rendah.");
        }

        $pendingOrders = \App\Models\Order::where('payment_status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->count();

        if ($pendingOrders > 0) {
            $this->info("{$pendingOrders} pesanan dengan pembayaran pending > 24 jam.");
        }

        $draftPO = \App\Models\PurchaseOrder::where('status', 'draft')
            ->where('created_at', '<', now()->subDays(3))
            ->count();

        if ($draftPO > 0) {
            $this->info("{$draftPO} purchase order draft > 3 hari.");
        }

        return self::SUCCESS;
    }
}
