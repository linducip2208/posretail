<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_templates', function (Blueprint $t) {
            if (! Schema::hasColumn('discount_templates', 'happy_start')) {
                $t->time('happy_start')->nullable()->after('end_date');
            }
            if (! Schema::hasColumn('discount_templates', 'happy_end')) {
                $t->time('happy_end')->nullable()->after('happy_start');
            }
            if (! Schema::hasColumn('discount_templates', 'days')) {
                $t->string('days', 20)->nullable()->after('happy_end'); // "1,2,3,4,5" (1=Senin)
            }
            if (! Schema::hasColumn('discount_templates', 'product_id')) {
                $t->foreignId('product_id')->nullable()->after('outlet_id')->constrained('products')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('discount_templates', function (Blueprint $t) {
            $t->dropColumn(['happy_start', 'happy_end', 'days', 'product_id']);
        });
    }
};
