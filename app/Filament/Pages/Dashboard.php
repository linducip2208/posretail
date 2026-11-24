<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Dashboard default = /tillora (lihat RedirectAdminHomeToTillora).
 * Class ini dipertahankan agar route /admin tetap terdaftar rapi;
 * request-nya selalu di-redirect oleh middleware sebelum render.
 */
class Dashboard extends BaseDashboard
{
}
