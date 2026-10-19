<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
    )

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.store' => \App\Http\Middleware\AuthenticateStore::class,
            'verified.store' => \App\Http\Middleware\EnsureStoreEmailIsVerified::class,
            'valid.email' => \App\Http\Middleware\EnsureStoreEmailIsValid::class,
        ]);
    })

    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('transactions:update-overdue')->daily();
        $schedule->command('customers:update-risk-levels')->daily();
        $schedule->command('reminders:send-due-date')->daily();
    })

    ->withExceptions(function (Exceptions $exceptions) {})->create();
