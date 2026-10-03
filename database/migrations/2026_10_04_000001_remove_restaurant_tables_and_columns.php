<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * RETAIL ONLY cleanup: hapus seluruh jejak database fitur restoran.
 *
 * - Drop tabel: reservations, kitchen_tickets, tables, table_areas
 * - Drop kolom orders.table_id (+ FK)
 * - Ubah default orders.order_type 'dine_in' → 'walk_in' (MySQL)
 *
 * Migration historis TIDAK diubah agar database production yang sudah
 * berjalan tetap aman; cleanup dilakukan di migration baru ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Lepas kolom orders.table_id (restoran) bila masih ada.
        if (Schema::hasColumn('orders', 'table_id')) {
            Schema::table('orders', function (Blueprint $table) {
                try {
                    $table->dropForeign(['table_id']);
                } catch (\Throwable) {
                    // FK mungkin bernama custom / sudah hilang — lanjut drop kolom.
                }
                $table->dropColumn('table_id');
            });
        }

        // 2. Drop tabel restoran (urutan: anak dulu baru induk).
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('kitchen_tickets');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('table_areas');

        // 3. Default order_type ritel untuk install baru (kolom dibuat di migration lama).
        if (Schema::hasColumn('orders', 'order_type') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `order_type` VARCHAR(255) NOT NULL DEFAULT 'walk_in'");
        }
    }

    public function down(): void
    {
        // Kembalikan struktur minimal agar rollback mekanis tetap jalan.
        // (Fitur restoran tetap TIDAK dihidupkan kembali di level aplikasi.)
        if (! Schema::hasTable('table_areas')) {
            Schema::create('table_areas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tables')) {
            Schema::create('tables', function (Blueprint $table) {
                $table->id();
                $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
                $table->foreignId('table_area_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('code')->unique();
                $table->integer('capacity')->default(4);
                $table->string('status')->default('available');
                $table->integer('sort_order')->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('orders', 'table_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('table_id')->nullable()->after('outlet_id')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasTable('kitchen_tickets')) {
            Schema::create('kitchen_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
                $table->string('ticket_number')->unique();
                $table->string('status')->default('pending');
                $table->text('items');
                $table->text('notes')->nullable();
                $table->timestamp('printed_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table) {
                $table->id();
                $table->string('reservation_number')->unique();
                $table->foreignId('table_id')->constrained('tables')->cascadeOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->string('customer_name');
                $table->string('customer_phone');
                $table->date('reservation_date');
                $table->time('time_slot');
                $table->integer('guest_count')->default(1);
                $table->string('status')->default('booked');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }
};
