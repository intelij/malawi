<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use App\Services\MembershipNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaidRegisterController extends Controller
{
    public function show()
    {
        return view('auth.register-paid');
    }

    public function createPaymentIntent(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $amount = setting('registration_fee') ?? 20; // USD

        $intent = PaymentIntent::create([
            'amount' => $amount * 100,
            'currency' => 'usd',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'purpose' => 'registration'
            ]
        ]);

        return response()->json([
            'clientSecret' => $intent->client_secret
        ]);
    }

    public function complete(Request $request)
    {
        $request->validate([
            'payment_intent' => 'required|string',
            'form.email' => 'required|email|unique:users,email',
            'form.password' => 'required|min:8|confirmed',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $intent = PaymentIntent::retrieve($request->payment_intent);

        if ($intent->status !== 'succeeded') {
            return response()->json([
                'success' => false,
                'message' => 'Payment not successful'
            ], 422);
        }

        if (Payment::where('reference', $intent->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment already processed'
            ], 409);
        }

        DB::transaction(function () use ($request, $intent, &$user) {

            $data = $request->form;

            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'phone'      => $data['phone'] ?? null,
                'password'   => Hash::make($data['password']),
            ]);

            $user->membership_number =
                MembershipNumberGenerator::generate($user);

            $user->save();

            $user->assignRole('Member');

            Payment::create([
                'user_id'      => $user->id,
                'amount'       => $intent->amount / 100,
                'payment_type' => 'Registration',
                'reference'    => $intent->id,
                'status'       => 'paid',
            ]);
        });

        Auth::login($user);

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard')
        ]);
    }
}
