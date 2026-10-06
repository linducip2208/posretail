<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Observability: setiap request mendapat X-Request-ID (atau teruskan
 * yang dikirim client) + masuk ke log context. Tidak mengubah body
 * response agar kontrak API dengan Flutter tetap stabil.
 */
class RequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->header('X-Request-ID') ?: (string) Str::uuid();
        // Batasi panjang agar header client nakal tidak membengkak log.
        $id = substr(preg_replace('/[^A-Za-z0-9\-]/', '', $id), 0, 64) ?: (string) Str::uuid();

        app()->instance('request_id', $id);
        Log::shareContext(['request_id' => $id]);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-Request-ID', $id);

        return $response;
    }
}
