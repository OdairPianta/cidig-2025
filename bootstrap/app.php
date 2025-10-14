<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ActiveUserMiddleware;
use App\Http\Middleware\ForceAddCorsHeaders;
use App\Http\Middleware\ValidateAiAccessToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'active_user' => ActiveUserMiddleware::class,
            'ai_access' => ValidateAiAccessToken::class,
        ]);
        
        // Replace default CSRF middleware with our custom one
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class
        );
        
        // Register our middleware to ensure it's available
        $middleware->use([
            ValidateAiAccessToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();