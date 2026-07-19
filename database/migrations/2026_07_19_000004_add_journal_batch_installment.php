<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── COA + Jurnal ──────────────────────────────
        if (!Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('name');
                $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense', 'cogs']);
                $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
                $table->enum('normal_balance', ['debit', 'credit'])->default('debit');
                $table->boolean('active')->default(true);
                $table->boolean('is_locked')->default(false);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('journal_entries')) {
            Schema::create('journal_entries', function (Blueprint $table) {
                $table->id();
                $table->string('journal_number')->unique();
                $table->date('journal_date');
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('description')->nullable();
                $table->enum('status', ['draft', 'posted', 'voided'])->default('draft');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('posted_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('journal_entry_items')) {
            Schema::create('journal_entry_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
                $table->foreignId('account_id')->constrained()->restrictOnDelete();
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // ─── Batch & Expiry ─────────────────────────────
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'batch_number')) {
                $table->string('batch_number')->nullable()->after('barcode');
            }
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'batch_number')) {
                $table->string('batch_number')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('stock_movements', 'expired_date')) {
                $table->date('expired_date')->nullable()->after('batch_number');
            }
        });

        // ─── Cicilan Schedule ───────────────────────────
        if (!Schema::hasTable('installment_schedules')) {
            Schema::create('installment_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('installment_id')->constrained()->cascadeOnDelete();
                $table->integer('sequence');
                $table->date('due_date');
                $table->decimal('amount', 15, 2);
                $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
                $table->date('paid_at')->nullable();
                $table->foreignId('paid_receipt_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_schedules');
        Schema::table('stock_movements', function ($t) {
            if (Schema::hasColumn('stock_movements', 'batch_number')) {
                $t->dropColumn('batch_number');
            }
            if (Schema::hasColumn('stock_movements', 'expired_date')) {
                $t->dropColumn('expired_date');
            }
        });
        Schema::table('products', function ($t) {
            if (Schema::hasColumn('products', 'batch_number')) {
                $t->dropColumn('batch_number');
            }
        });
        Schema::dropIfExists('journal_entry_items');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
    }
};
