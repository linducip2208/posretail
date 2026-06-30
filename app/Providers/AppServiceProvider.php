<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\LicenseClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            try {
                $timezone = \App\Models\SystemSetting::getValue('timezone', 'Asia/Jakarta');
                config(['app.timezone' => $timezone]);
                date_default_timezone_set($timezone);
            } catch (\Throwable $e) {
                //
            }
        });
    }
}
