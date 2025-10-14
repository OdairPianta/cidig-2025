<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

Route::post('/graphql', [\App\Http\Controllers\GraphQLController::class, 'query'])
    ->middleware([
        \Nuwave\Lighthouse\Http\Middleware\AcceptJson::class,
        \App\Http\Middleware\AiTokenMiddleware::class,
        \Nuwave\Lighthouse\Http\Middleware\AttemptAuthentication::class,
    ])
    ->name('graphql');