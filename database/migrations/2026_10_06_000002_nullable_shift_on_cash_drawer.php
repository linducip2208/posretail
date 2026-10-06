<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Refund bisa terjadi tanpa shift terbuka (controller mengirim
     * shift_id null). Kolom harus nullable agar tidak 500, dan
     * SET NULL agar histori kas tidak ikut terhapus saat shift dihapus.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            try {
                Schema::table('cash_drawer_transactions', function (Blueprint $table) {
                    $table->dropForeign(['shift_id']);
                });
            } catch (\Throwable $e) {
            }
            DB::statement('ALTER TABLE `cash_drawer_transactions` MODIFY `shift_id` BIGINT UNSIGNED NULL');
            try {
                DB::statement('ALTER TABLE `cash_drawer_transactions` ADD CONSTRAINT `cash_drawer_transactions_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL');
            } catch (\Throwable $e) {
            }

            return;
        }

        // SQLite (test): rebuild tabel dengan shift_id nullable.
        if (! Schema::hasTable('cash_drawer_transactions')) {
            return;
        }
        DB::statement('PRAGMA foreign_keys=OFF');
        try {
            DB::transaction(function () {
                DB::statement('ALTER TABLE `cash_drawer_transactions` RENAME TO `_cdt_old`');
                Schema::create('cash_drawer_transactions', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
                    $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                    $table->string('type');
                    $table->decimal('amount', 15, 2);
                    $table->string('payment_method')->nullable();
                    $table->text('notes')->nullable();
                    $table->timestamps();
                });
                DB::statement('INSERT INTO `cash_drawer_transactions` (`id`, `shift_id`, `order_id`, `type`, `amount`, `payment_method`, `notes`, `created_at`, `updated_at`) SELECT `id`, `shift_id`, `order_id`, `type`, `amount`, `payment_method`, `notes`, `created_at`, `updated_at` FROM `_cdt_old`');
                DB::statement('DROP TABLE `_cdt_old`');
            });
        } finally {
            DB::statement('PRAGMA foreign_keys=ON');
        }
    }

    public function down(): void
    {
        // Tidak reversible aman (baris dengan shift_id null melanggar NOT NULL).
    }
};
