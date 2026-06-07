<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\DecryptionException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Enforce application role security alias map layers
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // FIXED: Intercept local cryptographic engine exception drops cleanly
        $exceptions->render(function (DecryptionException $e) {
            Log::error('Secured payload execution failure encountered.', [
                'message' => $e->getMessage()
            ]);

            return response()->view('errors.400', [
                'message' => 'The data structural integrity verification signature check failed.'
            ], 400);
        });
    })->create();