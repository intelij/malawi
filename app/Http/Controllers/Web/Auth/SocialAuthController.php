<?php

namespace App\Http\Controllers\Web\Auth;

use Auth;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\User as SocialUser;
use Socialite;
use App\Events\User\LoggedIn;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\User\UserRepository;
use App\Services\Auth\Social\SocialManager;

class SocialAuthController extends Controller
{
    public function __construct(private readonly UserRepository $users, private readonly SocialManager $socialManager)
    {
        $this->middleware('guest');
    }

    /**
     * Redirect user to specified provider in order to complete the authentication process.
     */
    public function redirectToProvider(string $provider): RedirectResponse
    {
        session(['to' => request()->get('to')]);

        if (strtolower($provider) == 'facebook') {
            return Socialite::driver('facebook')->with(['auth_type' => 'rerequest'])->redirect();
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle response authentication provider.
     */
    public function handleProviderCallback(string $provider): RedirectResponse
    {
        if (request()->get('error')) {
            return redirect('login')
                ->withErrors(__('Something went wrong during the authentication process. Please try again.'));
        }

        $socialUser = $this->getUserFromProvider($provider);

        $user = $this->users->findBySocialId($provider, $socialUser->getId());

        if (! $user) {
            if (! setting('reg_enabled')) {
                return redirect('login')
                    ->withErrors(__('Only users who already created an account can log in.'));
            }

            if (! $socialUser->getEmail()) {
                return redirect('login')
                    ->withErrors(__('You have to provide your email address.'));
            }

            $user = $this->socialManager->associate($socialUser, $provider);

            event(new \Illuminate\Auth\Events\Registered($user));
        }

        return $this->loginAndRedirect($user);
    }

    /**
     * Get user from authentication provider.
     */
    private function getUserFromProvider(string $provider): SocialUser
    {
        return Socialite::driver($provider)->user();
    }

    /**
     * Log provided user in and redirect him to intended page.
     */
    private function loginAndRedirect(User $user): RedirectResponse
    {
        $redirectPage = session('to');
        $to = $redirectPage ? '?to='.$redirectPage : '';

        if ($user->isBanned()) {
            return redirect()->to('login'.$to)
                ->withErrors(__('Your account is banned by administrator.'));
        }

        if ($user->isWaitingApproval()) {
            return redirect()->to('login'.$to)
                ->withErrors(__('Your account is waiting approval from administrators.'));
        }

        if (setting('2fa.enabled') && $user->twoFactorEnabled()) {
            session()->put('auth.2fa.id', $user->id);

            if ($redirectPage) {
                session()->put('auth.redirect_to', $redirectPage);
            }

            return redirect()->route('auth.token');
        }

        Auth::login($user);

        event(new LoggedIn);

        if ($redirectPage) {
            return redirect()->to($redirectPage);
        }

        return redirect()->intended('/');
    }
}
