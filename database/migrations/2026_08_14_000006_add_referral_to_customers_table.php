<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'referral_code')) {
                $table->string('referral_code')->nullable()->after('phone')->unique();
            }
            if (! Schema::hasColumn('customers', 'referrer_id')) {
                $table->foreignId('referrer_id')->nullable()->after('referral_code')->constrained('customers')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['referrer_id']);
            $table->dropColumn(['referrer_id', 'referral_code']);
        });
    }
};
