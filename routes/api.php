<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\Password\RemindController;
use App\Http\Controllers\Api\Auth\Password\ResetController;
use App\Http\Controllers\Api\Auth\RegistrationController;
use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Api\Authorization\PermissionsController;
use App\Http\Controllers\Api\Authorization\RolePermissionsController;
use App\Http\Controllers\Api\Authorization\RolesController;
use App\Http\Controllers\Api\CountriesController;
use App\Http\Controllers\Api\Profile\AuthDetailsController;
use App\Http\Controllers\Api\Profile\AvatarController as ProfileAvatarController;
use App\Http\Controllers\Api\Profile\DetailsController;
use App\Http\Controllers\Api\Profile\SessionsController as ProfileSessionsController;
use App\Http\Controllers\Api\Profile\TwoFactorController as ProfileTwoFactorController;
use App\Http\Controllers\Api\SessionsController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\Users\AvatarController as UsersAvatarController;
use App\Http\Controllers\Api\Users\SessionsController as UsersSessionsController;
use App\Http\Controllers\Api\Users\TwoFactorController as UsersTwoFactorController;
use App\Http\Controllers\Api\Users\UsersController;

Route::post('login', [AuthController::class, 'token']);
Route::post('login/social', [SocialLoginController::class, 'index']);
Route::post('logout', [AuthController::class, 'logout']);

Route::post('register', [RegistrationController::class, 'index'])->middleware('registration');

Route::group(['middleware' => ['guest', 'password-reset']], function () {
    Route::post('password/remind', [RemindController::class, 'index']);
    Route::post('password/reset', [ResetController::class, 'index']);
});

Route::group(['middleware' => ['auth', 'registration']], function () {
    Route::post('email/resend', [VerificationController::class, 'resend']);
    Route::post('email/verify', [VerificationController::class, 'verify']);
});

Route::group(['middleware' => ['auth', 'verified', 'approved']], function () {
    Route::get('me', [DetailsController::class, 'index']);
    Route::patch('me/details', [DetailsController::class, 'update']);
    Route::patch('me/details/auth', [AuthDetailsController::class, 'update']);
    Route::post('me/avatar', [ProfileAvatarController::class, 'update']);
    Route::delete('me/avatar', [ProfileAvatarController::class, 'destroy']);
    Route::put('me/avatar/external', [ProfileAvatarController::class, 'updateExternal']);
    Route::get('me/sessions', [ProfileSessionsController::class, 'index']);

    Route::group(['middleware' => 'two-factor'], function () {
        Route::put('me/2fa', [ProfileTwoFactorController::class, 'update']);
        Route::post('me/2fa/verify', [ProfileTwoFactorController::class, 'verify']);
        Route::delete('me/2fa', [ProfileTwoFactorController::class, 'destroy']);
    });

    Route::get('stats', [StatsController::class, 'index']);

    Route::apiResource('users', UsersController::class)->except('show');
    Route::get('users/{userId}', [UsersController::class, 'show']);

    Route::post('users/{user}/avatar', [UsersAvatarController::class, 'update']);
    Route::put('users/{user}/avatar/external', [UsersAvatarController::class, 'updateExternal']);
    Route::delete('users/{user}/avatar', [UsersAvatarController::class, 'destroy']);

    Route::group(['middleware' => 'two-factor'], function () {
        Route::put('users/{user}/2fa', [UsersTwoFactorController::class, 'update']);
        Route::post('users/{user}/2fa/verify', [UsersTwoFactorController::class, 'verify']);
        Route::delete('users/{user}/2fa', [UsersTwoFactorController::class, 'destroy']);
    });

    Route::get('users/{user}/sessions', [UsersSessionsController::class, 'index']);

    Route::get('/sessions/{session}', [SessionsController::class, 'show']);
    Route::delete('/sessions/{session}', [SessionsController::class, 'destroy']);

    Route::apiResource('roles', RolesController::class)->except('show');
    Route::get('/roles/{roleId}', [RolesController::class, 'show']);

    Route::get('roles/{role}/permissions', [RolePermissionsController::class, 'show']);
    Route::put('roles/{role}/permissions', [RolePermissionsController::class, 'update']);

    Route::apiResource('permissions', PermissionsController::class);

    Route::get('/settings', [SettingsController::class, 'index']);

    Route::get('/countries', [CountriesController::class, 'index']);
});
