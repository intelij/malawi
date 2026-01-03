<?php

namespace App\Http\Controllers\Web\Profile;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Session\SessionRepository;

class SessionsController extends Controller
{
    public function __construct(private readonly SessionRepository $sessions)
    {
    }

    public function index(): View
    {
        return view('user.sessions', [
            'profile' => true,
            'user' => auth()->user(),
            'sessions' => $this->sessions->getUserSessions(auth()->id()),
        ]);
    }

    public function destroy($session): RedirectResponse
    {
        $this->sessions->invalidateSession($session->id);

        return redirect()->route('profile.sessions')
            ->withSuccess(__('Session invalidated successfully.'));
    }
}
