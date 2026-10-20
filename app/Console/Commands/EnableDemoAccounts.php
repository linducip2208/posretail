<?php

namespace App\Console\Commands;

use App\Models\Outlet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Mengaktifkan demo untuk jualan: membuat / mengaktifkan akun demo bawaan
 * (*@pos-retail.test / password) beserta role & akses outletnya.
 *
 * Aman untuk production: idempoten, TIDAK truncate, TIDAK menghapus data.
 * Kebalikan dari pos:disable-demo-accounts.
 */
class EnableDemoAccounts extends Command
{
    protected $signature = 'pos:enable-demo
        {--force : Jalankan tanpa konfirmasi}
        {--password=password : Kata sandi untuk semua akun demo}';

    protected $description = 'Aktifkan akun demo untuk jualan (aman, tanpa hapus data)';

    private array $demos = [
        'owner@pos-retail.test' => ['name' => 'Budi Hartono', 'role' => 'owner'],
        'manager@pos-retail.test' => ['name' => 'Sari Dewi', 'role' => 'manager'],
        'admin@pos-retail.test' => ['name' => 'Agus Prasetyo', 'role' => 'admin'],
        'kasir@pos-retail.test' => ['name' => 'Rina Safitri', 'role' => 'kasir'],
        'gudang@pos-retail.test' => ['name' => 'Doni Kusuma', 'role' => 'gudang'],
    ];

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Aktifkan 5 akun demo (*@pos-retail.test)? Data bisnis tidak akan dihapus.', true)) {
            $this->warn('Dibatalkan.');

            return self::SUCCESS;
        }

        // Pastikan roles & permissions ada (non-destruktif untuk data bisnis).
        if (Role::where('slug', 'owner')->doesntExist()) {
            $this->warn('Role belum ada — menjalankan RolePermissionSeeder dulu...');
            $this->call('db:seed', ['--class' => 'RolePermissionSeeder', '--force' => true]);
        }
        $roleIds = Role::pluck('id', 'slug');

        // Pastikan minimal 1 outlet aktif untuk demo.
        $outlet = Outlet::where('active', true)->orderBy('id')->first();
        if (! $outlet) {
            $outlet = Outlet::create([
                'name' => 'Toko Demo',
                'code' => 'DEMO01',
                'address' => 'Demo untuk presentasi penjualan',
                'active' => true,
            ]);
            $this->info("Outlet demo dibuat: {$outlet->name} (id {$outlet->id}).");
        }
        $allOutletIds = Outlet::where('active', true)->pluck('id')->all();

        $password = (string) $this->option('password');
        foreach ($this->demos as $email => $def) {
            $user = User::withTrashed()->where('email', $email)->first();
            if ($user?->trashed()) {
                $user->restore();
                $this->line("restore: {$email}");
            }
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $def['name'],
                    'password' => Hash::make($password),
                    'role' => $def['role'],
                    'active' => true,
                ]
            );
            if (! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            if (isset($roleIds[$def['role']])) {
                $user->roles()->syncWithoutDetaching([$roleIds[$def['role']]]);
            }
            // Owner: semua outlet. Selain itu: minimal outlet demo + jaga pivot lama.
            $user->outlets()->syncWithoutDetaching(
                $def['role'] === 'owner' ? $allOutletIds : [$outlet->id]
            );
        }

        $this->info('Akun demo aktif. Login dengan kata sandi: '.$password);
        $this->table(
            ['Email', 'Role', 'Outlet'],
            collect($this->demos)->map(fn ($def, $email) => [
                $email,
                $def['role'],
                $def['role'] === 'owner' ? 'semua ('.count($allOutletIds).')' : $outlet->name,
            ])->all()
        );

        if (! config('pos.demo_mode') && app()->environment('production')) {
            $this->warn('POS_DEMO_MODE=false — kotak kredensial demo disembunyikan di halaman publik.');
            $this->line('Untuk menampilkan tombol/box demo saat presentasi, set POS_DEMO_MODE=true di .env lalu: php artisan config:cache');
        }

        return self::SUCCESS;
    }
}
