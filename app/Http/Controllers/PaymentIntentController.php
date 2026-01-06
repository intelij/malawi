<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;

class PaymentIntentController extends Controller
{
    /**
     * Create Stripe PaymentIntent for registration
     */
    public function store(Request $request): JsonResponse
    {
        // Force JSON (prevents Blade / HTML responses)
        $request->headers->set('Accept', 'application/json');

        $data = $request->validate([
            'amount'     => ['required', 'numeric', 'min:1'],
            'email'      => ['nullable', 'email'],
            'first_name' => ['nullable', 'string'],
            'last_name'  => ['nullable', 'string'],
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = PaymentIntent::create([
                'amount'   => (int) round($data['amount'] * 100), // cents
                'currency' => 'gbp',
                'payment_method_types' => ['card'],
                'description' => 'User registration fee',
                'metadata' => [
                    'email'      => $data['email'] ?? null,
                    'first_name' => $data['first_name'] ?? null,
                    'last_name'  => $data['last_name'] ?? null,
                    'purpose'    => 'registration',
                ],
            ]);

            return response()->json([
                'clientSecret' => $intent->client_secret,
            ], 200);

        } catch (\Stripe\Exception\ApiErrorException $e) {

            Log::error('Stripe API error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Payment service unavailable.',
            ], 500);

        } catch (\Throwable $e) {

            Log::error('PaymentIntent creation failed', [
                'exception' => $e,
            ]);

            return response()->json([
                'error' => 'Unable to initiate payment.',
            ], 500);
        }
    }
}
