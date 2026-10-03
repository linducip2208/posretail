<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tambah soft-deletes ke tabel finansial/stok (sebelumnya trait tanpa kolom). */
    public function up(): void
    {
        foreach (['payments', 'stock_movements', 'customers', 'customer_deposits', 'supplier_payables', 'journal_entries'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['payments', 'stock_movements', 'customers', 'customer_deposits', 'supplier_payables', 'journal_entries'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};
