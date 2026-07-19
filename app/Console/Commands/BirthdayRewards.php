<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class BirthdayRewards extends Command
{
    protected $signature = 'pos:birthday-rewards';

    protected $description = 'Give loyalty points to customers with birthday today';

    public function handle(): int
    {
        $today = now()->format('m-d');
        $count = 0;
        $bonusPoints = 50;

        $birthdayCustomers = Customer::where('active', true)
            ->whereNotNull('birth_date')
            ->whereRaw("DATE_FORMAT(birth_date, '%m-%d') = ?", [$today])
            ->get();

        if ($birthdayCustomers->isEmpty()) {
            $anniversaryCustomers = Customer::where('active', true)
                ->whereNull('birth_date')
                ->whereRaw("DATE_FORMAT(created_at, '%m-%d') = ?", [$today])
                ->get();

            foreach ($anniversaryCustomers as $customer) {
                LoyaltyPoint::create([
                    'customer_id' => $customer->id,
                    'order_id' => null,
                    'points_earned' => $bonusPoints,
                    'points_redeemed' => 0,
                    'balance' => ($customer->total_points ?? 0) + $bonusPoints,
                    'description' => 'Bonus anniversary member — ' . now()->format('d F Y'),
                ]);

                $customer->increment('total_points', $bonusPoints);
                $count++;
                $this->info("+{$bonusPoints} pts untuk {$customer->name} (anniversary)");
            }
        } else {
            foreach ($birthdayCustomers as $customer) {
                LoyaltyPoint::create([
                    'customer_id' => $customer->id,
                    'order_id' => null,
                    'points_earned' => $bonusPoints,
                    'points_redeemed' => 0,
                    'balance' => ($customer->total_points ?? 0) + $bonusPoints,
                    'description' => 'Bonus ulang tahun — ' . now()->format('d F Y'),
                ]);

                $customer->increment('total_points', $bonusPoints);
                $count++;
                $this->info("+{$bonusPoints} pts untuk {$customer->name} (ultah)");
            }
        }

        if ($count === 0) {
            $this->info('Tidak ada customer yang berulang tahun hari ini.');
        } else {
            $body = "{$count} customer menerima +{$bonusPoints} poin loyalty ulang tahun.";
            $recipients = User::whereIn('role', ['owner', 'manager', 'admin'])->get();
            foreach ($recipients as $user) {
                Notification::make()
                    ->title('Bonus Ulang Tahun')
                    ->body($body)
                    ->success()
                    ->sendToDatabase($user);
            }
            $this->info("Notifikasi dikirim ke {$recipients->count()} admin.");
        }

        return self::SUCCESS;
    }
}
