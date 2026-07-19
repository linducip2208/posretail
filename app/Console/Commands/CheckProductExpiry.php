<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class CheckProductExpiry extends Command
{
    protected $signature = 'pos:check-expiry';

    protected $description = 'Check product expiry dates and send alerts';

    public function handle(): int
    {
        $alerts = [];

        // Expired products
        $expired = Product::where('active', true)
            ->whereNotNull('expired_date')
            ->where('expired_date', '<=', now())
            ->get();

        foreach ($expired as $product) {
            $alerts[] = "EXPIRED: {$product->name} — expired {$product->expired_date->format('d/m/Y')}";
            $this->warn("EXPIRED: {$product->name}");
        }

        // Expiring in 30 days
        $expiring30 = Product::where('active', true)
            ->whereNotNull('expired_date')
            ->where('expired_date', '>', now())
            ->where('expired_date', '<=', now()->addDays(30))
            ->get();

        foreach ($expiring30 as $product) {
            $days = now()->diffInDays($product->expired_date, false);
            $alerts[] = "EXPIRING: {$product->name} — expires in {$days} days ({$product->expired_date->format('d/m/Y')})";
            $this->line("EXPIRING: {$product->name} — {$days} days left");
        }

        // FEFO recommendation: stock movement picking order
        $fefoProducts = Product::where('active', true)
            ->whereNotNull('expired_date')
            ->where('current_stock', '>', 0)
            ->orderBy('expired_date')
            ->get();

        if ($fefoProducts->isNotEmpty()) {
            $this->info('FEFO Pick Order (expiring first):');
            foreach ($fefoProducts as $i => $p) {
                $this->line("  " . ($i + 1) . ". {$p->name} — stok: {$p->current_stock}, expired: {$p->expired_date->format('d/m/Y')}");
            }
        }

        // Send notification
        if (!empty($alerts)) {
            $body = implode("\n", array_slice($alerts, 0, 5));
            if (count($alerts) > 5) {
                $body .= "\n... dan " . (count($alerts) - 5) . " lainnya.";
            }

            $recipients = User::whereIn('role', ['owner', 'manager', 'admin', 'gudang'])->get();
            foreach ($recipients as $user) {
                Notification::make()
                    ->title('Peringatan Expired Produk')
                    ->body($body)
                    ->danger()
                    ->sendToDatabase($user);
            }
            $this->info("Notification sent to {$recipients->count()} users.");
        } else {
            $this->info('No products near expiry.');
        }

        return self::SUCCESS;
    }
}
