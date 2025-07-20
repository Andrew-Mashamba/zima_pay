<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Security middleware stack for API protection (order matters)
        // Note: Apply security middleware manually to specific routes as needed
        
        // Register individual middleware aliases
        $middleware->alias([
            'api.auth' => \App\Http\Middleware\ApiAuthentication::class,
            'ip.blocking' => \App\Http\Middleware\IpBlockingMiddleware::class,
            'rate.limit.advanced' => \App\Http\Middleware\AdvancedRateLimit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
