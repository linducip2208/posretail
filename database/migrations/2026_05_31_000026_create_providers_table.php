<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('payment'); // payment, sms, email, storage, ai
            $table->string('api_format'); // rest-redirect, rest-api, qr-static
            $table->string('base_url')->nullable();
            $table->text('api_key_encrypted')->nullable();
            $table->text('api_secret_encrypted')->nullable();
            $table->text('merchant_id')->nullable();
            $table->text('client_id')->nullable();
            $table->json('extra_headers')->nullable();
            $table->json('extra_config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->foreignId('provider_id')->nullable()->after('code')
                ->constrained('providers')->nullOnDelete();
            $table->boolean('is_gateway')->default(false)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropColumn(['provider_id', 'is_gateway']);
        });
        Schema::dropIfExists('providers');
    }
};
