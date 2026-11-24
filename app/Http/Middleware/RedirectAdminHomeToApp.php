<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dashboard default = /app (full POS Retail, standalone Blade).
 * Redirect HTTP biasa (bukan Livewire navigate) supaya <script> vendor
 * (Bootstrap dropdown, Chart.js) selalu dieksekusi browser.
 */
class RedirectAdminHomeToApp
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin')) {
            return redirect('/app');
        }

        return $next($request);
    }
}
