<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'check.ip' => \App\Http\Middleware\CheckIp::class, // 👈 added
        ]);

        // Append Inertia to web group
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // ✅ Add IP check as global middleware (runs on every request)
        $middleware->append([
            \App\Http\Middleware\CheckIp::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();