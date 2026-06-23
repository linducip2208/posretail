<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 4. Table areas (restaurant floor plan)
        Schema::create('table_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // 5. Tables
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('table_area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('capacity')->default(4);
            $table->string('status')->default('available')->comment('available, occupied, reserved, maintenance');
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // 6. Link orders to tables
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('table_id')->nullable()->after('outlet_id')->constrained()->nullOnDelete();
        });

        // 7. Raw materials (bahan baku)
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('cost_per_unit', 15, 2)->default(0);
            $table->decimal('current_stock', 15, 2)->default(0);
            $table->decimal('min_stock', 15, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // 8. Recipe items (bahan baku per product)
        Schema::create('recipe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 15, 4);
            $table->timestamps();
        });

        // 9. Discount templates
        Schema::create('discount_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->comment('percent, fixed, buy_x_get_y');
            $table->decimal('value', 15, 2);
            $table->decimal('min_purchase', 15, 2)->default(0);
            $table->integer('buy_quantity')->nullable()->comment('for buy_x_get_y');
            $table->integer('get_quantity')->nullable()->comment('for buy_x_get_y');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // 10. Installment payments
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->integer('installment_number');
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->string('status')->default('pending')->comment('pending, paid, overdue');
            $table->timestamps();
        });

        // 11. Kitchen tickets (cetak tiket dapur)
        Schema::create('kitchen_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_number')->unique();
            $table->string('status')->default('pending')->comment('pending, preparing, ready, served');
            $table->text('items')->comment('JSON array of items for kitchen');
            $table->text('notes')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->dropColumn('table_id');
        });

        Schema::dropIfExists('kitchen_tickets');
        Schema::dropIfExists('installments');
        Schema::dropIfExists('discount_templates');
        Schema::dropIfExists('recipe_items');
        Schema::dropIfExists('raw_materials');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('table_areas');
    }
};
