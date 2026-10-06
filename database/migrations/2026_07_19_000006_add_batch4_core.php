<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard hasTable: sebagian DB lama terbuat di luar migrasi.
        // Di database fresh semua tabel dibuat normal; di DB drift
        // yang sudah punya tabelnya, pembuatan dilewati dengan aman.

        // ─── 1. Faktur Pajak ─────────────────────────────
        if (! Schema::hasTable('tax_invoices')) {
        Schema::create('tax_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('customer_npwp', 20)->nullable();
            $table->string('customer_name');
            $table->text('customer_address')->nullable();
            $table->decimal('dpp', 15, 2)->comment('Dasar Pengenaan Pajak');
            $table->decimal('ppn_amount', 15, 2)->comment('PPN 11%');
            $table->decimal('total_amount', 15, 2);
            $table->string('reference_number')->nullable()->comment('Nomor seri faktur pajak');
            $table->date('invoice_date');
            $table->enum('status', ['draft', 'issued', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        } // end if tax_invoices

        // ─── 2. Multi-Level Pricing ─────────────────────
        if (! Schema::hasTable('volume_pricings')) {
        Schema::create('volume_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
            $table->integer('min_qty');
            $table->integer('max_qty')->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->enum('discount_type', ['fixed', 'percent'])->default('fixed');
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        } // end if volume_pricings

        // ─── 3. Customer Credit Limit ───────────────────
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'credit_limit')) {
                $table->decimal('credit_limit', 15, 2)->default(0)->after('deposit_balance');
            }
            if (!Schema::hasColumn('customers', 'outstanding_balance')) {
                $table->decimal('outstanding_balance', 15, 2)->default(0)->after('credit_limit');
            }
        });

        // ─── 4. Gift Card / Voucher ─────────────────────
        if (! Schema::hasTable('gift_cards')) {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->enum('type', ['nominal', 'discount_percent']);
            $table->decimal('value', 15, 2);
            $table->decimal('min_purchase', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2);
            $table->date('valid_from');
            $table->date('valid_until');
            $table->enum('status', ['active', 'used', 'expired', 'cancelled'])->default('active');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('max_usage')->default(1);
            $table->integer('used_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        } // end if gift_cards

        if (! Schema::hasTable('gift_card_usages')) {
        Schema::create('gift_card_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_used', 15, 2);
            $table->timestamps();
        });

        } // end if gift_card_usages

        // ─── 5. Delivery Tracking ───────────────────────
        if (! Schema::hasTable('deliveries')) {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'packed', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->text('shipping_address');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->dateTime('packed_at')->nullable();
            $table->dateTime('shipped_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamps();
        });

        } // end if deliveries

        // ─── 6. Sales Target ────────────────────────────
        if (! Schema::hasTable('sales_targets')) {
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('year');
            $table->integer('month');
            $table->decimal('target_amount', 15, 2);
            $table->timestamps();
            $table->unique(['outlet_id', 'user_id', 'year', 'month']);
        });

        } // end if sales_targets

        // ─── 7. Waste / Damage ──────────────────────────
        if (! Schema::hasTable('write_offs')) {
        Schema::create('write_offs', function (Blueprint $table) {
            $table->id();
            $table->string('writeoff_number')->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total_loss', 15, 2);
            $table->enum('reason', ['expired', 'damaged', 'loss', 'other']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        } // end if write_offs

        // ─── 8. Consignment Stock ───────────────────────
        if (! Schema::hasTable('consignments')) {
        Schema::create('consignments', function (Blueprint $table) {
            $table->id();
            $table->string('consignment_number')->unique();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->integer('sold_quantity')->default(0);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->date('start_date');
            $table->date('settlement_date')->nullable();
            $table->enum('status', ['active', 'settled', 'returned'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        } // end if consignments

        // ─── 9. Table Reservation ───────────────────────
        // (Legacy restoran; tabel reservations di-drop migrasi remove_restaurant.
        // Guard hasTable agar DB drift yang sudah punya tabelnya tetap lolos.)
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
            $table->enum('status', ['booked', 'arrived', 'cancelled', 'no_show'])->default('booked');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        } // end if reservations
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('consignments');
        Schema::dropIfExists('write_offs');
        Schema::dropIfExists('sales_targets');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('gift_card_usages');
        Schema::dropIfExists('gift_cards');
        Schema::table('customers', fn ($t) => $t->dropColumn(['credit_limit', 'outstanding_balance']));
        Schema::dropIfExists('volume_pricings');
        Schema::dropIfExists('tax_invoices');
    }
};
