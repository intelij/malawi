<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

class EnsureUserIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user() && $request->user()->isWaitingApproval()) {
            return $request->expectsJson()
                ? abort(403, __('Your account is waiting approval from administrators.'))
                : Redirect::guest(URL::route('approval.notice'));
        }

        return $next($request);
    }
}
