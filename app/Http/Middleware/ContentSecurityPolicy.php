<?php

namespace App\Http\Middleware;

use Closure;

class ContentSecurityPolicy
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self';
             script-src 'self' https://js.stripe.com https://m.stripe.network blob:;
             frame-src https://js.stripe.com https://hooks.stripe.com;
             connect-src 'self' https://api.stripe.com https://m.stripe.network;
             img-src 'self' data: https://*.stripe.com;
             style-src 'self' 'unsafe-inline';"
        );

        return $response;
    }
}
