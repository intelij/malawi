<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;

class RegisterCompleteController extends Controller
{
    /**
     * Complete registration AFTER payment success
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'first_name'            => ['required', 'string', 'max:255'],
            'last_name'             => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'password'              => ['required', 'confirmed', 'min:8'],
            'amount'                => ['required', 'numeric'],
            'payment_intent_id'     => ['required', 'string'],
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = PaymentIntent::retrieve($data['payment_intent_id']);

            if ($intent->status !== 'succeeded') {
                return back()->withErrors([
                    'payment' => 'Payment not completed. Please try again.',
                ]);
            }

        } catch (\Throwable $e) {

            Log::error('Payment verification failed', [
                'exception' => $e,
            ]);

            return back()->withErrors([
                'payment' => 'Unable to verify payment.',
            ]);
        }

        // ✅ Create user
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
        ]);

        // Optional: store payment reference
        // Payment::create([...]);

        Auth::login($user);

        return redirect()->route('home')
            ->with('success', 'Registration successful. Welcome!');
    }
}
