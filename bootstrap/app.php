<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

/**
 * Production safety: if the app is running with SQLite and the database file
 * path is invalid or missing, create it on the fly to prevent 500s on first
 * request (e.g., POST /login) when the DB is accessed.
 *
 * This primarily helps when a committed .env points to an absolute path like
 * /app/database/database.sqlite which doesn't exist in the runtime image
 * layout. We normalize to Laravel's database_path('database.sqlite') and
 * ensure the directory/file exist.
 */
try {
    // NOTE: APP_KEY generation removed - must be set in .env file
    // Auto-generating keys was causing CSRF validation failures due to
    // key mismatches between requests. Always use a fixed key in .env.

    // Ensure critical storage directories exist and are writable
    $storageDirs = [
        __DIR__.'/../storage/framework',
        __DIR__.'/../storage/framework/sessions',
        __DIR__.'/../storage/framework/cache',
        __DIR__.'/../storage/framework/views',
        __DIR__.'/../bootstrap/cache',
    ];
    foreach ($storageDirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @chmod($dir, 0775);
    }

    $conn = getenv('DB_CONNECTION') ?: null;
    if ($conn && strtolower($conn) === 'sqlite') {
        $configured = getenv('DB_DATABASE') ?: null;
        $defaultPath = __DIR__.'/../database/database.sqlite';

        $usePath = $configured ?: $defaultPath;

        // If the configured path points to a non-existent file, fall back to default
        if (!is_string($usePath) || $usePath === '' || (!file_exists($usePath) && str_starts_with((string) $usePath, '/app/database/'))) {
            $usePath = $defaultPath;
            // Update environment so Laravel uses the corrected path
            putenv('DB_DATABASE='.$usePath);
            $_ENV['DB_DATABASE'] = $usePath;
            $_SERVER['DB_DATABASE'] = $usePath;
        }

        // Ensure directory exists and file is present
        $dir = dirname($usePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (!file_exists($usePath)) {
            @touch($usePath);
        }
    }
} catch (\Throwable $e) {
    // Non-fatal: never break boot; logging will capture any issues later
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Proxy trust disabled for local testing
        // TODO: Re-enable for production deployment
        // $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR
        //     | Request::HEADER_X_FORWARDED_HOST
        //     | Request::HEADER_X_FORWARDED_PORT
        //     | Request::HEADER_X_FORWARDED_PROTO
        //     | Request::HEADER_X_FORWARDED_AWS_ELB);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
