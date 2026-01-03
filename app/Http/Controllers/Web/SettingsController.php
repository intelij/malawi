<?php

namespace App\Http\Controllers\Web;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Setting;
use App\Events\Settings\Updated as SettingsUpdated;
use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display general settings page.
     */
    public function general(): View
    {
        return view('settings.general');
    }

    /**
     * Display Authentication & Registration settings page.
     */
    public function auth(): View
    {
        return view('settings.auth');
    }

    public function update(Request $request): RedirectResponse
    {
        $this->updateSetting($request->except('_token'));

        return back()->withSuccess(__('Settings updated successfully.'));
    }

    private function updateSetting(array $input): void
    {
        foreach ($input as $key => $value) {
            Setting::set($key, $value);
        }

        Setting::save();

        event(new SettingsUpdated);
    }

    public function enableTwoFactor(): RedirectResponse
    {
        $this->updateSetting(['2fa.enabled' => true]);

        return back()->withSuccess(__('Two-Factor Authentication enabled successfully.'));
    }

    public function disableTwoFactor(): RedirectResponse
    {
        $this->updateSetting(['2fa.enabled' => false]);

        return back()->withSuccess(__('Two-Factor Authentication disabled successfully.'));
    }

    public function enableApproval(): RedirectResponse
    {
        $this->updateSetting(['approval.enabled' => true]);

        return back()->withSuccess(__('User Confirmation Flow enabled successfully.'));
    }

    public function disableApproval(): RedirectResponse
    {
        $this->updateSetting(['approval.enabled' => false]);

        return back()->withSuccess(__('User Confirmation Flow disabled successfully.'));
    }

    public function enablePasswordChange(): RedirectResponse
    {
        $this->updateSetting(['password-change.enabled' => true]);

        return back()->withSuccess(__('Force Password Change for users enabled successfully.'));
    }

    public function disablePasswordChange(): RedirectResponse
    {
        $this->updateSetting(['password-change.enabled' => false]);

        return back()->withSuccess(__('Force Password Change for users disabled successfully.'));
    }


    public function enableCaptcha(): RedirectResponse
    {
        $this->updateSetting(['registration.captcha.enabled' => true]);

        return back()->withSuccess(__('reCAPTCHA enabled successfully.'));
    }

    public function disableCaptcha(): RedirectResponse
    {
        $this->updateSetting(['registration.captcha.enabled' => false]);

        return back()->withSuccess(__('reCAPTCHA disabled successfully.'));
    }

    public function notifications(): View
    {
        return view('settings.notifications');
    }
}
