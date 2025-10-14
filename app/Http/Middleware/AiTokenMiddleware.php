<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $expectedToken = env('AI_ACCESS_TOKEN');

        if (!$token || !$expectedToken || !hash_equals($expectedToken, $token)) {
            return response()->json([
                'errors' => [
                    [
                        'message' => 'Invalid or missing AI access token',
                        'extensions' => [
                            'category' => 'authentication'
                        ]
                    ]
                ]
            ], 401);
        }

        return $next($request);
    }
}
