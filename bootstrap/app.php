<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Models\ErrorLog;
use Illuminate\Support\Facades\Schema;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\CheckMaintenanceMode::class);
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'admin.activity' => \App\Http\Middleware\LogAdminActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (\Throwable $exception): void {
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                return;
            }

            try {
                if (! Schema::hasTable('error_logs')) {
                    return;
                }

                $request = app()->bound('request') ? request() : null;
                ErrorLog::create([
                    'exception_class' => get_class($exception),
                    'message' => mb_substr($exception->getMessage(), 0, 10000),
                    'level' => 'error',
                    'method' => $request?->method(),
                    'url' => $request ? mb_substr($request->fullUrl(), 0, 2000) : null,
                    'user_id' => $request?->user()?->id,
                    'ip_address' => $request?->ip(),
                    'trace' => mb_substr($exception->getTraceAsString(), 0, 20000),
                ]);
            } catch (\Throwable) {
                // Le journal d'erreurs ne doit jamais empêcher le traitement de l'exception originale.
            }
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
