<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tipe order + queue number + waiter/employee assignment
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_type')->default('dine_in')->after('order_status')->comment('dine_in, takeaway, delivery');
            $table->string('queue_number')->nullable()->after('order_type');
            $table->decimal('deposit_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('remaining_amount', 15, 2)->default(0)->after('deposit_amount');
            $table->boolean('is_installment')->default(false)->after('remaining_amount');
            $table->string('installment_period')->nullable()->after('is_installment')->comment('weekly, biweekly, monthly');
            $table->integer('installment_count')->default(1)->after('installment_period');
            $table->foreignId('employee_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->text('order_notes')->nullable()->after('notes');
        });

        // 2. Expired date for products
        Schema::table('products', function (Blueprint $table) {
            $table->date('expired_date')->nullable()->after('current_stock');
        });

        // 3. Employee attendance
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->string('status')->default('present')->comment('present, late, absent, leave, sick');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['order_type', 'queue_number', 'deposit_amount', 'remaining_amount', 'is_installment', 'installment_period', 'installment_count', 'employee_id', 'order_notes']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('expired_date');
        });

        Schema::dropIfExists('attendances');
    }
};
