<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', [
                "default-src 'self'",
                "script-src 'self' https://js.stripe.com https://m.stripe.network blob:",
                "frame-src https://js.stripe.com",
                "connect-src https://api.stripe.com",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data:",
            ])
        );

        return $response;
    }
}
