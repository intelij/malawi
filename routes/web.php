<?php

use App\Http\Controllers\PaidRegisterController;
use App\Http\Controllers\PaymentIntentController;
use \App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\RegisterCompleteController;
use \App\Http\Controllers\Web\Auth\RegisterController;
use \App\Http\Controllers\Web\Auth\TwoFactorTokenController;
use \App\Http\Controllers\Web\Auth\SocialAuthController;
use \App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\Profile\AvatarController as ProfileAvatarController;
use App\Http\Controllers\Web\Profile\DetailsController as ProfileDetailsController;
use App\Http\Controllers\Web\Profile\LoginDetailsController as ProfileLoginDetailsController;
use App\Http\Controllers\Web\Profile\ProfileController;
use App\Http\Controllers\Web\Profile\SessionsController as ProfileSessionsController;
use App\Http\Controllers\Web\TwoFactorController;
use App\Http\Controllers\Web\Users\UsersController;
use App\Http\Controllers\Web\Users\DetailsController as UsersDetailsController;
use App\Http\Controllers\Web\Users\LoginDetailsController as UsersLoginDetailsController;
use App\Http\Controllers\Web\Users\AvatarController as UsersAvatarController;
use App\Http\Controllers\Web\Users\SessionsController as UsersSessionsController;
use Vanguard\UserActivity\Http\Controllers\Web\ActivityController as UsersActivityController;
use App\Http\Controllers\Web\Authorization\RolesController;
use App\Http\Controllers\Web\Authorization\RolePermissionsController;
use App\Http\Controllers\Web\Authorization\PermissionsController;
use App\Http\Controllers\Web\SettingsController;
use Vanguard\UserActivity\Http\Controllers\Web\ActivityController as WebActivityController;
use App\Http\Controllers\Web\InstallController;
use App\Http\Controllers\Web\Users\PaymentController;
use App\Http\Middleware\DebugCsp;
use Vanguard\UserActivity\Http\Controllers\Web\ActivityController;

/**
 * Authentication
 */
Route::get('login', [LoginController::class, 'show']);
Route::post('login', [LoginController::class, 'login'])->name('login');
Route::get('logout', [LoginController::class, 'logout'])->name('auth.logout');

Route::middleware([DebugCsp::class])->group(function () {

    Route::get('/register', [PaidRegisterController::class, 'show'])
        ->name('register');

    Route::post('/register/payment-intent', [PaidRegisterController::class, 'createPaymentIntent'])
        ->name('payment.intent');

    Route::post('/register/complete', [PaidRegisterController::class, 'complete'])
        ->name('register.complete');
});

Route::group(['middleware' => ['registration', 'guest', DebugCsp::class]], function () {
    // Route::get('register', [RegisterController::class, 'show']);
    // Route::post('register', [RegisterController::class, 'register']);


    // Create PaymentIntent
    Route::post('/payments/intent', [PaymentIntentController::class, 'store'])
        ->name('pay.intent');

    // Complete registration
    Route::post('/register/complete', RegisterCompleteController::class)
        ->name('register.complete');

    Route::get('/register', [PaidRegisterController::class, 'show'])
        ->name('register');

    Route::post('/register/intent', [PaidRegisterController::class, 'createPaymentIntent'])
        ->name('register.intent');

    Route::post('/register/complete', [PaidRegisterController::class, 'complete'])
        ->name('register.complete');

});

Route::emailVerification();

Route::group(['middleware' => ['password-reset', 'guest']], function () {
    Route::resetPassword();
});

/**
 * Two-Factor Authentication
 */
Route::group(['middleware' => 'two-factor'], function () {
    Route::get('auth/two-factor-authentication', [TwoFactorTokenController::class, 'show'])
        ->name('auth.token');
    Route::post('auth/two-factor-authentication', [TwoFactorTokenController::class, 'update'])
        ->name('auth.token.validate');
});

/** Fundraiser */
Route::get('fundraiser', 'Users\UsersController@fundraiser')->name('fundraiser.list');
Route::post('fundraiser', 'Users\UsersController@newfundraiser')->name('fundraiser.create');
Route::delete('fundraiser/destroy/{fundraiser}', 'Users\UsersController@destroy_fundraiser')->name('fundraiser.destroy');


Route::get('export-users', 'Users\UsersController@export')->name('export.users');
Route::post('export-fundraiser', 'Users\UsersController@exportFundraiser')->name('fundraiser.export');

Route::get('stale-users', 'Users\UsersController@noActivityLogs')->name('stale.users');

Route::get('invoices', 'Users\UsersController@createInvoice')->name('retrieve-invoice');
Route::get('get-invoices', 'Users\UsersController@createInvoice')->name('get-invoices');
/**
 * Social Login
 */
Route::get('auth/{provider}/login', [SocialAuthController::class, 'redirectToProvider'])
    ->name('social.login');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);

// Route::get('/upload-excel', (ExcelController::class, 'showUploadForm');
Route::post('upload-excel', 'Users\UsersController@uploadExcel')->name('upload.excel');

/**
 * Impersonate Routes
 */
Route::group(['middleware' => 'auth'], function () {
    Route::impersonate();
});

Route::get('/approval-notice', fn () =>view('auth.approval'))
    ->middleware(['auth', 'verified'])
    ->name('approval.notice');

Route::group(['middleware' => ['auth', 'verified']], function () {

    Route::get('/checkout', [PaymentController::class, 'checkout'])->name('checkout');
    Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent'])->name('payment.intent');
    // Route::post('/payment-success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/payment-success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');

});

Route::group(['middleware' => ['auth', 'verified', 'approved', 'password-change']], function () {

    /**
     * Dashboard
     */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /**
     * User Profile
     */
    Route::group(['prefix' => 'profile', 'namespace' => 'Profile'], function () {
        Route::get('/', [ProfileController::class, 'show'])->name('profile');
        Route::get('activity', [ActivityController::class, 'show'])->name('profile.activity');
        Route::put('details', [ProfileDetailsController::class, 'update'])->name('profile.update.details');

        Route::post('avatar', [ProfileAvatarController::class, 'update'])->name('profile.update.avatar');
        Route::post('avatar/external', [ProfileAvatarController::class, 'updateExternal'])
            ->name('profile.update.avatar-external');

        Route::put('login-details', [ProfileLoginDetailsController::class, 'update'])
            ->name('profile.update.login-details');

        Route::get('sessions', [ProfileSessionsController::class, 'index'])
            ->name('profile.sessions')
            ->middleware('session.database');

        Route::delete('sessions/{session}/invalidate', [ProfileSessionsController::class, 'destroy'])
            ->name('profile.sessions.invalidate')
            ->middleware('session.database');
    });

    /**
     * Two-Factor Authentication Setup
     */
    Route::group(['middleware' => 'two-factor'], function () {
        Route::post('two-factor/enable', [TwoFactorController::class, 'enable'])
            ->name('2fa.enable');

        Route::post('two-factor/verify', [TwoFactorController::class, 'verify'])
            ->name('2fa.verify')
            ->middleware('verify-2fa-code');

        Route::post('two-factor/disable', [TwoFactorController::class, 'disable'])
            ->name('2fa.disable');
    });

    /**
     * User Management
     */
    Route::resource('users', UsersController::class)
        ->except('update')->middleware('permission:users.manage');

    Route::group(['prefix' => 'users/{user}', 'middleware' => 'permission:users.manage'], function () {
        Route::put('update/approve', [UsersDetailsController::class, 'approve'])
            ->name('users.update.approve');

        Route::put('update/details', [UsersDetailsController::class, 'update'])
            ->name('users.update.details');
        Route::put('update/login-details', [UsersLoginDetailsController::class, 'update'])
            ->name('users.update.login-details');

        Route::post('update/avatar', [UsersAvatarController::class, 'update'])
            ->name('user.update.avatar');
        Route::post('update/avatar/external', [UsersAvatarController::class, 'updateExternal'])
            ->name('user.update.avatar.external');

        Route::get('sessions', [UsersSessionsController::class, 'index'])
            ->name('user.sessions')
            ->middleware('session.database');

        Route::delete('sessions/{session}/invalidate', [UsersSessionsController::class, 'destroy'])
            ->name('user.sessions.invalidate')
            ->middleware('session.database');

        Route::post('two-factor/enable', [TwoFactorController::class, 'enable'])
            ->name('user.two-factor.enable');
        Route::post('two-factor/disable', [TwoFactorController::class, 'disable'])
            ->name('user.two-factor.disable');
    });

    /**
     * Roles & Permissions
     */
    Route::group(['namespace' => 'Authorization'], function () {
        Route::resource('roles', RolesController::class)
            ->except('show')
            ->middleware('permission:roles.manage');

        Route::post('permissions/save', [RolePermissionsController::class, 'update'])
            ->name('permissions.save')
            ->middleware('permission:permissions.manage');

        Route::resource('permissions', PermissionsController::class)
            ->middleware('permission:permissions.manage');
    });

    /**
     * Settings
     */
    Route::get('settings', [SettingsController::class, 'general'])->name('settings.general')
        ->middleware('permission:settings.general');

    Route::post('settings/general', [SettingsController::class, 'update'])->name('settings.general.update')
        ->middleware('permission:settings.general');

    Route::get('settings/auth', [SettingsController::class, 'auth'])->name('settings.auth')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth', [SettingsController::class, 'update'])->name('settings.auth.update')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth/2fa/enable', [SettingsController::class, 'enableTwoFactor'])
        ->name('settings.auth.2fa.enable')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth/2fa/disable', [SettingsController::class, 'disableTwoFactor'])
        ->name('settings.auth.2fa.disable')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth/password-change/enable', [SettingsController::class, 'enablePasswordChange'])
        ->name('settings.auth.password-change.enable')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth/password-change/disable', [SettingsController::class, 'disablePasswordChange'])
        ->name('settings.auth.password-change.disable')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth/approval/enable', [SettingsController::class, 'enableApproval'])
        ->name('settings.auth.approval.enable')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth/approval/disable', [SettingsController::class, 'disableApproval'])
        ->name('settings.auth.approval.disable')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth/registration/captcha/enable', [SettingsController::class, 'enableCaptcha'])
        ->name('settings.registration.captcha.enable')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth/registration/captcha/disable', [SettingsController::class, 'disableCaptcha'])
        ->name('settings.registration.captcha.disable')
        ->middleware('permission:settings.auth');

    Route::get('settings/notifications', [SettingsController::class, 'notifications'])
        ->name('settings.notifications')
        ->middleware('permission:settings.notifications');

    Route::post('settings/notifications', [SettingsController::class, 'update'])
        ->name('settings.notifications.update')
        ->middleware('permission:settings.notifications');

    /**
     * Activity Log
     */
    Route::get('activity', [WebActivityController::class, 'index'])->name('activity.index')
        ->middleware('permission:users.activity');

    Route::get('activity/user/{user}/log', [UsersActivityController::class, 'index'])->name('activity.user')
        ->middleware('permission:users.activity');
});

/**
 * Installation
 */
Route::group(['prefix' => 'install'], function () {
    Route::get('/', [InstallController::class, 'index'])->name('install.start');
    Route::get('requirements', [InstallController::class, 'requirements'])->name('install.requirements');
    Route::get('permissions', [InstallController::class, 'permissions'])->name('install.permissions');
    Route::get('database', [InstallController::class, 'databaseInfo'])->name('install.database');
    Route::get('start-installation', [InstallController::class, 'installation'])->name('install.installation.start');
    Route::post('start-installation', [InstallController::class, 'installation'])->name('install.installation');
    Route::post('install-app', [InstallController::class, 'install'])->name('install.install');
    Route::get('complete', [InstallController::class, 'complete'])->name('install.complete');
    Route::get('error', [InstallController::class, 'error'])->name('install.error');
});
