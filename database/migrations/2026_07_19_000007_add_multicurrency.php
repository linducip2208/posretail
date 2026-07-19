<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 12. Multi-Currency ─────────────────────────
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 3)->default('IDR')->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'exchange_rate')) {
                $table->decimal('exchange_rate', 15, 4)->default(1)->after('currency');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'currency')) {
                $table->string('currency', 3)->default('IDR')->after('selling_price');
            }
        });

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 3);
            $table->decimal('rate', 15, 4);
            $table->date('effective_date');
            $table->timestamps();
            $table->unique(['currency', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
        Schema::table('products', fn ($t) => $t->dropColumn('currency'));
        Schema::table('orders', fn ($t) => $t->dropColumn(['currency', 'exchange_rate']));
    }
};
