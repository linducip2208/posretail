<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Assembly / Produksi ─────────────────────────
        Schema::create('assembly_orders', function (Blueprint $table) {
            $table->id();
            $table->string('assembly_number')->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('assembly_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained('raw_materials')->restrictOnDelete();
            $table->decimal('quantity', 15, 2);
            $table->timestamps();
        });

        // ─── Employee Roster ─────────────────────────────
        Schema::create('rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week')->comment('0=minggu, 1=senin, ..., 6=sabtu');
            $table->time('shift_start');
            $table->time('shift_end');
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->timestamps();
        });

        // ─── Bank Reconciliation ─────────────────────────
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('account_number')->nullable();
            $table->date('transaction_date');
            $table->string('description');
            $table->string('reference')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->boolean('is_matched')->default(false);
            $table->unsignedBigInteger('matched_transaction_id')->nullable();
            $table->string('matched_transaction_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_statements');
        Schema::dropIfExists('rosters');
        Schema::dropIfExists('assembly_order_items');
        Schema::dropIfExists('assembly_orders');
    }
};
