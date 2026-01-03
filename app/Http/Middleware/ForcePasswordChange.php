<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\Authorization\PasswordChangeManager;

class ForcePasswordChange
{
    public function __construct(private readonly PasswordChangeManager $forcePasswordChange)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$this->forcePasswordChange->isPasswordChangeForcedForUser($request->user())) {
            return $next($request);
        }

        if (in_array($request->route()->getName(), ['profile', 'profile.update.login-details'])) {
            return $next($request);
        }

        return $request->expectsJson() ?
            abort(403, __('Before continuing please update your password and set a new one.')) :
            redirect(route('profile'));
    }
}
