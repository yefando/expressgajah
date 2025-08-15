<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'teacher' => \App\Http\Middleware\IsTeacher::class,
        ]);


        $middleware->api(prepend: [
            'cors',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();