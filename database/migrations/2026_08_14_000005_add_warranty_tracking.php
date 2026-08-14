<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'warranty_months')) {
                $table->integer('warranty_months')->default(0)->after('serial_tracking')->comment('Masa garansi dalam bulan');
            }
        });

        Schema::table('serial_numbers', function (Blueprint $table) {
            if (! Schema::hasColumn('serial_numbers', 'warranty_expires_at')) {
                $table->date('warranty_expires_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('serial_numbers', function (Blueprint $table) {
            $table->dropColumn('warranty_expires_at');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('warranty_months');
        });
    }
};
