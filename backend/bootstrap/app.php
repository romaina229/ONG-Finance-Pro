<?php

declare(strict_types=1);

use App\Http\Middleware\RequirePermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(api: __DIR__.'/../routes/api.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['permission' => RequirePermission::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // JSON API exceptions are negotiated automatically.
    })
    ->create();
