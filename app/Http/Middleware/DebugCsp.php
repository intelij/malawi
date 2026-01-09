<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugCsp
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 🚨 TEMPORARY DEBUG ONLY
        $response->headers->set(
            'Content-Security-Policy',
            "script-src * 'unsafe-inline' 'unsafe-eval' blob: data:;"
        );

        return $response;
    }
}
