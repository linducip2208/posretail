<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Held carts (hold & recall)
        Schema::create('held_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label')->nullable();
            $table->json('items')->comment('JSON: [{id, name, price, qty}]');
            $table->timestamps();
        });

        // 2. Split payments support - add to payments
        Schema::table('payments', function (Blueprint $table) {
            $table->integer('split_index')->default(0)->after('amount');
            $table->string('reference_number')->nullable()->change();
            $table->string('status')->default('pending')->change();
        });

        // 3. Shifts & cash drawers
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->decimal('starting_cash', 15, 2)->default(0);
            $table->decimal('ending_cash', 15, 2)->nullable();
            $table->decimal('expected_cash', 15, 2)->nullable();
            $table->decimal('difference', 15, 2)->nullable();
            $table->string('status')->default('active')->comment('active, closed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_drawer_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->comment('sale, refund, cash_in, cash_out');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Membership tiers
        Schema::create('membership_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('min_spent', 15, 2)->comment('Minimum total spent to reach tier');
            $table->integer('min_orders')->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('point_multiplier', 5, 2)->default(1);
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Add tier + poin expired to customers
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('membership_tier_id')->nullable()->after('customer_group_id')->constrained()->nullOnDelete();
            $table->date('points_expire_at')->nullable()->after('total_points');
        });

        // 5. Returns/Refunds
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->comment('customer_return, supplier_return');
            $table->decimal('total_amount', 15, 2);
            $table->string('reason')->nullable();
            $table->string('status')->default('pending')->comment('pending, approved, completed, rejected');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        // 6. Accounts Payable (hutang AP)
        Schema::create('supplier_payables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('status')->default('unpaid')->comment('unpaid, partial, paid, overdue');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_payable_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable();
            $table->string('reference_number')->nullable();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Audit Log
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action')->comment('create, update, delete, login, logout');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('payable_payments');
        Schema::dropIfExists('supplier_payables');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['membership_tier_id']);
            $table->dropColumn(['membership_tier_id', 'points_expire_at']);
        });
        Schema::dropIfExists('membership_tiers');
        Schema::dropIfExists('cash_drawer_transactions');
        Schema::dropIfExists('shifts');
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('split_index');
        });
        Schema::dropIfExists('held_carts');
    }
};
