<?php

namespace App\Http\Controllers\Web\Users;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Session\SessionRepository;

class SessionsController extends Controller
{
    public function __construct(private readonly SessionRepository $sessions)
    {
        $this->middleware('permission:users.manage');
    }

    public function index(User $user): View
    {
        return view('user.sessions', [
            'adminView' => true,
            'user' => $user,
            'sessions' => $this->sessions->getUserSessions($user->id),
        ]);
    }

    public function destroy(User $user, $session): RedirectResponse
    {
        $this->sessions->invalidateSession($session->id);

        return redirect()->route('user.sessions', $user->id)
            ->withSuccess(__('Session invalidated successfully.'));
    }
}
