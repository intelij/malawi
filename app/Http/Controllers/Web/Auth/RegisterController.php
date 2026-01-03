<?php

namespace App\Http\Controllers\Web\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Repositories\Role\RoleRepository;
use App\Repositories\User\UserRepository;

class RegisterController extends Controller
{
    public function __construct(private readonly UserRepository $users)
    {
        $this->middleware('registration')->only('show', 'register');
    }

    public function show(): View
    {
        return view('auth.register', [
            'socialProviders' => config('auth.social.providers'),
        ]);
    }

    public function register(RegisterRequest $request, RoleRepository $roles): RedirectResponse
    {
        $user = $this->users->create(
            array_merge(
                $request->validFormData(),
                ['role_id' => $roles->findByName(Role::DEFAULT_USER_ROLE)->id],
            )
        );

        event(new Registered($user));

        $message = setting('reg_email_confirmation')
            ? __('Your account is created successfully! Please confirm your email.')
            : __('Your account is created successfully!');

        if (setting('approval.enabled')) {
            return redirect('/login')->with('success', $message);
        } else {
            \Auth::login($user);
            return redirect('/')->with('success', $message);
        }
    }
}
