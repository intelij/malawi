<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        // Generate per-request nonce
        $nonce = base64_encode(random_bytes(16));

        // Make nonce available to Blade
        app()->instance('cspNonce', $nonce);

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', [
                "default-src 'self'",

                // Stripe scripts ONLY
                "script-src 'self' 'nonce-{$nonce}' https://js.stripe.com https://m.stripe.network",

                // Needed for Stripe Elements iframe loader
                "script-src-elem 'self' 'nonce-{$nonce}' https://js.stripe.com https://m.stripe.network",

                // Stripe iframes
                "frame-src https://js.stripe.com https://m.stripe.network",

                // Stripe API + your own backend
                "connect-src 'self' https://api.stripe.com https://m.stripe.network https://q.stripe.com",

                // Stripe images (fraud detection)
                "img-src 'self' data: https://q.stripe.com",

                // Allow inline styles (Bootstrap / Stripe injects styles)
                "style-src 'self' 'unsafe-inline'",

                "font-src 'self' data:",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ])
        );

        return $response;
    }
}
