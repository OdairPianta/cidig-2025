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
        // Database integrity errors (duplicates, records still in use) from the admin panel
        // are shown as a notification instead of a 500 page that also loses the session context.
        $exceptions->render(function (\Illuminate\Database\UniqueConstraintViolationException|\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            if (! $request->is('admin*', 'livewire*') || $e->getCode() !== '23000') {
                return null;
            }

            report($e);

            $message = str_contains($e->getMessage(), '1062')
                ? 'Já existe um registro com estes dados. Verifique se não está duplicado.'
                : 'Este registro está vinculado a outros conteúdos e não pôde ser salvo ou removido.';

            if ($request->hasHeader('X-Livewire')) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->back()->withInput()->with('error', $message);
        });
    })->create();