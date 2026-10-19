<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Remediasi: menonaktifkan akun demo bawaan (pos-retail.test) di database.
 *
 * Jalankan di production bila database sempat di-seed dengan DemoDataSeeder
 * sehingga akun berkata sandi lemah tidak bisa dipakai login.
 */
class DisableDemoAccounts extends Command
{
    protected $signature = 'pos:disable-demo-accounts {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Nonaktifkan akun demo bawaan (owner/manager/admin/kasir/gudang@pos-retail.test)';

    public function handle(): int
    {
        $emails = [
            'owner@pos-retail.test',
            'manager@pos-retail.test',
            'admin@pos-retail.test',
            'kasir@pos-retail.test',
            'gudang@pos-retail.test',
        ];

        $users = User::whereIn('email', $emails)->get();

        if ($users->isEmpty()) {
            $this->info('Tidak ada akun demo yang ditemukan — tidak ada tindakan.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Email', 'Active'],
            $users->map(fn (User $u) => [$u->id, $u->email, $u->active ? 'yes' : 'no'])->all()
        );

        if (! $this->option('force') && ! $this->confirm('Nonaktifkan semua akun demo di atas?', true)) {
            $this->warn('Dibatalkan.');

            return self::SUCCESS;
        }

        $count = User::whereIn('email', $emails)->update(['active' => false]);

        $this->info("{$count} akun demo dinonaktifkan.");
        $this->line('Catatan: pastikan akun owner/admin asli sudah ada dan kata sandinya kuat sebelum deploy.');

        return self::SUCCESS;
    }
}
