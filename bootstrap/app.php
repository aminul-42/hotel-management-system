<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
        Route::middleware('web')->group(base_path('routes/admin.php'));
        Route::middleware('web')->group(base_path('routes/frontdesk.php'));
        Route::middleware('web')->group(base_path('routes/customer.php'));
    },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        'frontdesk' => \App\Http\Middleware\EnsureUserIsFrontDesk::class,
        'customer' => \App\Http\Middleware\EnsureUserIsCustomer::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
