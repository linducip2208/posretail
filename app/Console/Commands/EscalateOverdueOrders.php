<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class EscalateOverdueOrders extends Command
{
    protected $signature = 'pos:escalate-overdue';

    protected $description = 'Escalate overdue orders and mark as cancelled';

    public function handle(): int
    {
        $orders = Order::whereIn('order_status', ['pending', 'processing'])
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        foreach ($orders as $order) {
            $order->update([
                'order_status' => 'cancelled',
                'notes' => ($order->notes ? $order->notes . "\n" : '') . 'Otomatis dibatalkan — melebihi 24 jam.',
            ]);

            $this->info("Order {$order->order_number} cancelled (overdue).");
        }

        $this->info("Escalated {$orders->count()} overdue orders.");

        return self::SUCCESS;
    }
}
