<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('customers', 'birth_date')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->date('birth_date')->nullable()->after('phone');
            });
        }
    }

    public function down(): void
    {
        Schema::table('customers', fn ($t) => $t->dropColumn('birth_date'));
    }
};
