<?php

namespace App\Http\Middleware;

// use App\Services\MembershipNumberGenerator;

use App\Support\MembershipNumberGenerator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function Illuminate\Log\log;

class EnsureMembershipNumber
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && is_null($user->membership_number)) {
            $user->update([
                'membership_number' => MembershipNumberGenerator::generate(),
            ]);
        }

        // log("WE FIRE THIS",[$request]);

        return $next($request);
    }
}
