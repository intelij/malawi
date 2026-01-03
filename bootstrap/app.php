<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            if (config('auth.expose_api')) {
                Route::middleware('api')
                    ->namespace('App\Http\Controllers\Api')
                    ->prefix('api')
                    ->name("api.")
                    ->group(base_path('routes/api.php'));
            }

            Route::middleware([
                \App\Http\Middleware\VerifyInstallation::class,
                'web'
            ])
                ->namespace('App\Http\Controllers\Web')
                ->group(base_path('routes/web.php'));
        },
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'registration' => \App\Http\Middleware\RegistrationEnabled::class,
            'social.login' => \App\Http\Middleware\SocialLogin::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermissions::class,
            'session.database' => \App\Http\Middleware\DatabaseSession::class,
            'two-factor' => \App\Http\Middleware\TwoFactorEnabled::class,
            'verify-2fa-code' => \App\Http\Middleware\VerifyTwoFactorCode::class,
            'password-reset' => \App\Http\Middleware\PasswordResetEnabled::class,
            'banned' => \App\Http\Middleware\CheckIfBanned::class,
            'approved' => \App\Http\Middleware\EnsureUserIsApproved::class,
            'password-change' => \App\Http\Middleware\ForcePasswordChange::class,
        ]);

        $middleware->web([
            \App\Http\Middleware\SetLocale::class,
            'banned',
        ]);

        $middleware->api([
            \App\Http\Middleware\UseApiGuard::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:60,1',
            'banned',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
