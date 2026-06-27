<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->bigInteger('min_stock')->default(0)->change();
            $table->bigInteger('max_stock')->default(0)->change();
            $table->bigInteger('current_stock')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('min_stock')->default(0)->change();
            $table->integer('max_stock')->default(0)->change();
            $table->integer('current_stock')->default(0)->change();
        });
    }
};
