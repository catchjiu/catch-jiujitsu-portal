<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

// Capture fatal PHP errors (not thrown as exceptions) for HTTP requests.
// This helps debug "500" cases where Laravel exception handlers don't run.
register_shutdown_function(function (): void {
    if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
        return;
    }

    $error = error_get_last();
    if (!is_array($error)) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($error['type'] ?? null, $fatalTypes, true)) {
        return;
    }

    $payload = [
        'time' => date('c'),
        'type' => $error['type'] ?? null,
        'message' => $error['message'] ?? null,
        'file' => $error['file'] ?? null,
        'line' => $error['line'] ?? null,
        'url' => $_SERVER['REQUEST_URI'] ?? null,
    ];

    try {
        @file_put_contents(
            storage_path('app/runtime-last-fatal.json'),
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    } catch (\Throwable) {
        // Best-effort only.
    }
});

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'member' => \App\Http\Middleware\MemberMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhook/line',
            'liff/session', // LINE in-app browser may not send session cookie; id_token is the auth
        ]);

        // Add locale middleware to web group
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e): void {
            // Skip noisy non-server errors.
            if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
                return;
            }

            $payload = [
                'time' => now()->toIso8601String(),
                'type' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => array_slice($e->getTrace(), 0, 20),
            ];

            // Only store "last exception" for HTTP requests. Console commands (e.g. migrations)
            // can fail safely and would otherwise overwrite the real web error we want to debug.
            if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
                try {
                    @file_put_contents(
                        storage_path('app/runtime-last-exception.json'),
                        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                    );
                } catch (\Throwable) {
                    // Best-effort debug capture only.
                }
            }

            Log::error('Unhandled runtime exception captured', $payload);
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            $runtimeDebug = filter_var(env('APP_RUNTIME_DEBUG', false), FILTER_VALIDATE_BOOL);
            if (! $runtimeDebug) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            if ($status < 500) {
                return null;
            }

            $payload = [
                'time' => now()->toIso8601String(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'type' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => array_slice($e->getTrace(), 0, 20),
            ];

            if ($request->expectsJson() || $request->query('format') === 'json') {
                return response()->json($payload, $status);
            }

            $content = "RUNTIME DEBUG ENABLED\n\n".json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            return response(
                '<pre style="white-space: pre-wrap; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;">'
                .e($content).
                '</pre>',
                $status
            );
        });
    })->create();
