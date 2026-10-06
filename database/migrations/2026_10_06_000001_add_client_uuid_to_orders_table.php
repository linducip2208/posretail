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
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('client_uuid', 100)->nullable()->after('order_number');
            $table->unique('client_uuid', 'orders_client_uuid_unique');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique('orders_client_uuid_unique');
            $table->dropColumn('client_uuid');
        });
    }
};
