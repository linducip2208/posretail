<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\RequestId::class);

        $middleware->web(append: [
            \App\Http\Middleware\RequirePair::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'pos/checkout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->is('pos/*'),
        );

        // Jangan bocorkan SQL/stack trace ke client API.
        // Detail teknis tetap di log server beserta request-id.
        $exceptions->render(function (\Illuminate\Database\QueryException $e, Request $request) {
            if (! ($request->is('api/*') || $request->is('pos/*'))) {
                return null;
            }

            $rid = app()->bound('request_id') ? app('request_id') : null;
            \Illuminate\Support\Facades\Log::error('DB error: '.$e->getMessage(), [
                'request_id' => $rid,
                'user_id' => $request->user()?->id,
                'path' => $request->path(),
            ]);

            return response()->json([
                'message' => 'Terjadi gangguan database, coba lagi.',
                'code' => 'DB_ERROR',
                'request_id' => $rid,
            ], 500);
        });
    })->create();
