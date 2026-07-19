<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\SupplierPayable;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class SendReminderNotifications extends Command
{
    protected $signature = 'pos:send-reminders';

    protected $description = 'Send reminder notifications for low stock and pending orders';

    public function handle(): int
    {
        $notified = 0;

        $lowStockProducts = Product::where('active', true)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->get();

        if ($lowStockProducts->isNotEmpty()) {
            $recipients = User::whereIn('role', ['owner', 'manager', 'admin', 'gudang'])->get();
            foreach ($recipients as $user) {
                Notification::make()
                    ->title('Stok Rendah')
                    ->body($lowStockProducts->count() . ' produk mencapai stok minimum: ' . $lowStockProducts->take(5)->pluck('name')->join(', ') . ($lowStockProducts->count() > 5 ? ' dan lainnya' : ''))
                    ->warning()
                    ->sendToDatabase($user);
            }
            $notified += $recipients->count();
            $this->info("Low stock alert: {$lowStockProducts->count()} products, sent to {$recipients->count()} users.");
        }

        $overdueOrders = Order::where('payment_status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        if ($overdueOrders->isNotEmpty()) {
            $recipients = User::whereIn('role', ['owner', 'manager', 'admin'])->get();
            foreach ($recipients as $user) {
                Notification::make()
                    ->title('Pembayaran Tertunda')
                    ->body($overdueOrders->count() . ' pesanan memiliki pembayaran pending > 24 jam.')
                    ->danger()
                    ->sendToDatabase($user);
            }
            $notified += $recipients->count();
            $this->info("Overdue payment alert: {$overdueOrders->count()} orders, sent to {$recipients->count()} users.");
        }

        $overduePayables = SupplierPayable::where('status', 'pending')
            ->where('due_date', '<', now())
            ->get();

        if ($overduePayables->isNotEmpty()) {
            $recipients = User::whereIn('role', ['owner', 'manager', 'admin'])->get();
            foreach ($recipients as $user) {
                Notification::make()
                    ->title('Hutang Supplier Jatuh Tempo')
                    ->body($overduePayables->count() . ' tagihan supplier telah jatuh tempo.')
                    ->danger()
                    ->sendToDatabase($user);
            }
            $notified += $recipients->count();
            $this->info("Overdue payable alert: {$overduePayables->count()} payables, sent to {$recipients->count()} users.");
        }

        $draftPO = PurchaseOrder::where('status', 'draft')
            ->where('created_at', '<', now()->subDays(3))
            ->count();

        if ($draftPO > 0) {
            $this->info("{$draftPO} purchase order draft > 3 hari — info only, no notification sent.");
        }

        $this->info("Total notifications sent: {$notified}.");

        return self::SUCCESS;
    }
}
