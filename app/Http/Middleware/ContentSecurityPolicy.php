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
                "script-src 'self' 'unsafe-inline' https://js.stripe.com https://m.stripe.network blob:",
                "script-src-elem 'self' 'unsafe-inline' https://js.stripe.com https://m.stripe.network blob:",
                "style-src 'self' 'unsafe-inline'",
                "frame-src https://js.stripe.com https://m.stripe.network",
                "connect-src 'self' https://api.stripe.com https://m.stripe.network https://q.stripe.com",
                "img-src 'self' data: https://q.stripe.com",
                "font-src 'self' data:",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ])
        );

        return $response;
    }
}
