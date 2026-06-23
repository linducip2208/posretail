<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;

class SendPendingNotifications extends Command
{
    protected $signature = 'pos:send-notifications';

    protected $description = 'Send pending notifications from queue';

    public function handle(): int
    {
        $pendingPayments = Payment::where('status', 'pending')
            ->where('created_at', '>=', now()->subHours(48))
            ->with(['order.customer', 'paymentMethod'])
            ->get();

        foreach ($pendingPayments as $payment) {
            $this->line("Notification: Payment #{$payment->id} for order {$payment->order?->order_number} still pending.");
        }

        $this->info("Processed {$pendingPayments->count()} pending payment notifications.");

        return self::SUCCESS;
    }
}
