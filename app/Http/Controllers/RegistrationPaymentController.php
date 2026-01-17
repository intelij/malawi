<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class RegistrationPaymentController extends Controller
{
    public function showForm()
    {
        return view('auth.register-paid');
        return view('register-payment');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $registrationFee = 50; // AUD

        // Create user as unpaid
        $user = User::create([
            'first_name' => $request->name,
            'last_name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_paid' => false,
            'status' => 'Unconfirmed',
        ]);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => [
                        'name' => 'User Registration Fee',
                    ],
                    'unit_amount' => $registrationFee * 100,
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('register.success', $user->id),
            'cancel_url' => route('register.fail', $user->id),
        ]);

        $user->update([
            'stripe_session_id' => $session->id,
        ]);

        return redirect($session->url);
    }
}
