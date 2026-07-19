<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. Purchase Requisition (PR) ───────────────
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number')->unique();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_needed')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'ordered'])->default('draft');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_requisition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->integer('quantity');
            $table->integer('current_stock_snapshot')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        // ─── 2. Budget ──────────────────────────────────
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->integer('month');
            $table->decimal('revenue_target', 15, 2)->default(0);
            $table->decimal('expense_limit', 15, 2)->default(0);
            $table->decimal('actual_revenue', 15, 2)->default(0);
            $table->decimal('actual_expense', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['outlet_id', 'year', 'month']);
        });

        // ─── 3. Asset Management ───────────────────────
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('category')->default('equipment')->comment('equipment,furniture,vehicle,building,it');
            $table->date('purchase_date');
            $table->decimal('purchase_value', 15, 2);
            $table->decimal('current_value', 15, 2);
            $table->integer('useful_life_months')->default(48);
            $table->decimal('monthly_depreciation', 15, 2)->default(0);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->enum('status', ['active', 'disposed', 'maintenance'])->default('active');
            $table->string('location')->nullable()->comment('physical location');
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->date('maintenance_date');
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('type')->default('repair')->comment('repair,inspection,upgrade');
            $table->text('description')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->timestamps();
        });

        // ─── 4. Bin Location (multi-warehouse) ──────────
        Schema::create('bin_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name')->comment('Rak A, Gudang B, etc');
            $table->string('zone')->nullable()->comment('Area: depan, belakang, atas');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['outlet_id', 'code']);
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'bin_location_id')) {
                $table->foreignId('bin_location_id')->nullable()->after('outlet_id')->constrained('bin_locations')->nullOnDelete();
            }
        });

        // ─── 5. Supplier Scoring ────────────────────────
        Schema::create('supplier_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('on_time')->default(5)->comment('1-5');
            $table->tinyInteger('quality')->default(5)->comment('1-5');
            $table->tinyInteger('price_competitiveness')->default(5)->comment('1-5');
            $table->tinyInteger('communication')->default(5)->comment('1-5');
            $table->decimal('avg_score', 3, 1)->default(5);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ─── 6. Supplier Contracts ─────────────────────
        Schema::create('supplier_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('contract_number')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('value', 15, 2)->default(0);
            $table->text('terms')->nullable();
            $table->string('payment_terms')->nullable()->comment('Net 30, COD, etc');
            $table->enum('status', ['active', 'expired', 'terminated'])->default('active');
            $table->string('document_path')->nullable();
            $table->timestamps();
        });

        // ─── 7. Customer Device Tokens (push notif) ────
        Schema::create('customer_device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->string('platform')->default('android')->comment('android, ios, web');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_device_tokens');
        Schema::dropIfExists('supplier_contracts');
        Schema::dropIfExists('supplier_ratings');
        Schema::table('products', fn ($t) => $t->dropForeign(['bin_location_id']));
        Schema::table('products', fn ($t) => $t->dropColumn('bin_location_id'));
        Schema::dropIfExists('bin_locations');
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('purchase_requisition_items');
        Schema::dropIfExists('purchase_requisitions');
    }
};
