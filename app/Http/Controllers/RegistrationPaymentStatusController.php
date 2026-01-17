<?php

namespace App\Http\Controllers;

use App\Models\User;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class RegistrationPaymentStatusController extends Controller
{
    public function success($userId)
    {
        $user = User::findOrFail($userId);

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::retrieve($user->stripe_session_id);

        if ($session->payment_status !== 'paid') {
            return redirect()->route('register')->withErrors('Payment not completed.');
        }

        $user->update([
            'is_paid' => true,
            'paid_at' => now(),
        ]);

        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Registration successful. Welcome!');
    }

    public function fail($userId)
    {
        $user = User::findOrFail($userId);

        // Optional: delete unpaid user
        $user->delete();

        return redirect()->route('register')
            ->withErrors('Payment cancelled.');
    }
}
