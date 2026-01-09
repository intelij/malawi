<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use App\Models\Role;
use App\Repositories\Role\RoleRepository;
use App\Repositories\User\UserRepository;
use App\Services\MembershipNumberGenerator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaidRegisterController extends Controller
{
    public function __construct(private readonly UserRepository $users)
    {
        $this->middleware('registration')->only('show', 'register');
    }

    public function show()
    {
        return view('auth.register-paid');
    }

    public function createPaymentIntent(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $amount = setting('registration_fee') ?? 6; // GBP

        $intent = PaymentIntent::create([
            // 'amount' => $amount * 100,
            'currency' => 'gbp',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'purpose' => 'registration'
            ]
        ]);

        return response()->json([
            'clientSecret' => $intent->client_secret
        ]);
    }

    public function complete(Request $request, RoleRepository $roles)
    {
        $request->validate([
            'payment_intent' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
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

        DB::transaction(function () use ($request, $intent, &$user, $roles) {

            $user = $this->users->create([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'status'      => 'Unconfirmed',
                'password'   => Hash::make($request->password),
                'role_id'   => $roles->findByName(Role::DEFAULT_USER_ROLE)->id,
            ]);

            $user->membership_number =
                MembershipNumberGenerator::generate($user);

            $user->save();

            Payment::create([
                'user_id'           => $user->id,
                'payment_type'      => 'Registration Payment',
                'amount'            => $intent->amount,
                'reference'         => $intent->id,
                'fundraiser_id'     => 0,
                'membership_number' => $user->membership_number,
                'verified'          => 1,
                'image_name'        => "Online Stripe Payment {$intent->id}",
            ]);

        });


        Auth::login($user);

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
