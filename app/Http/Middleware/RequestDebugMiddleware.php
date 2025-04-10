<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestDebugMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        Log::info('Requisição iniciada', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip()
        ]);

        $response = $next($request);

        $duration = microtime(true) - $startTime;
        Log::info('Requisição finalizada', [
            'duration' => $duration,
            'status' => $response->status()
        ]);

        return $response;
    }
}
