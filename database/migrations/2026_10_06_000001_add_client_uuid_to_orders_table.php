<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Idempotency key untuk order dari aplikasi kasir (offline queue).
     * Nullable agar order lama/web tidak wajib mengisi; unique agar
     * double-submit / retry sync tidak membuat order ganda.
     */
    public function up(): void
    {
        // Guard: idempoten untuk DB yang terbuat di luar migrasi.
        if (! Schema::hasColumn('orders', 'client_uuid')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('client_uuid', 100)->nullable()->after('order_number');
            });
        }
        try {
            Schema::table('orders', function (Blueprint $table): void {
                $table->unique('client_uuid', 'orders_client_uuid_unique');
            });
        } catch (\Throwable $e) {
            // Index sudah ada — lanjut.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropUnique('orders_client_uuid_unique');
            });
        } catch (\Throwable $e) {
        }
        if (Schema::hasColumn('orders', 'client_uuid')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('client_uuid');
            });
        }
    }
};
