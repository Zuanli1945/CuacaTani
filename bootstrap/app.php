<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// Ensure SQLite database file exists in writable directory (Vercel serverless uses /tmp)
$databasePath = '/tmp/database.sqlite';
$tmpDir = dirname($databasePath);
if (!is_dir($tmpDir)) {
    @mkdir($tmpDir, 0755, true);
}
if (!file_exists($databasePath)) {
    @touch($databasePath);
}

// Override database path for SQLite
putenv("DB_DATABASE=$databasePath");

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
