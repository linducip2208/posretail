<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\User;
use Filament\Notifications\Notification;
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

        if ($pendingPayments->isNotEmpty()) {
            $recipients = User::whereIn('role', ['owner', 'manager', 'admin'])->get();
            foreach ($recipients as $user) {
                Notification::make()
                    ->title('Pembayaran Pending')
                    ->body("{$pendingPayments->count()} pembayaran masih dalam status pending dalam 48 jam terakhir.")
                    ->warning()
                    ->sendToDatabase($user);
            }
            $this->info("Processed {$pendingPayments->count()} pending payment notifications, sent to {$recipients->count()} users.");
        }

        return self::SUCCESS;
    }
}
