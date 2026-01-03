<?php

namespace App\Http\Controllers\Web\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentConfirmation;
use App\Models\Fundraiser;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Stripe\Event;
use Stripe\Invoice as StripeInvoice;
use Stripe\InvoiceItem;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Show the checkout page
     */
    public function checkout()
    {
        return view('payments.checkout');
    }

    public function createPaymentIntent(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $user = auth()->user();

            $data = $request->validate([
                'fundraiser_id'   => 'required|integer|exists:fundraisers,id',
                'linked_accounts' => 'nullable|string',
                'amount'          => 'required|numeric|min:1',
                'reference'       => 'nullable|string',
            ]);

            $amount = (float) $data['amount'];
            $fundraiserId = $data['fundraiser_id'];
            $linkedAccounts = array_filter(explode(',', trim($data['linked_accounts'] ?? '')));

            // 🔹 Create customer on Stripe
            $customer = \Stripe\Customer::create([
                'email' => $user->email,
                'name'  => trim($user->first_name . ' ' . $user->last_name),
            ]);

            // 🔹 Create payment intent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int) round($amount * 100), // Always convert to cents
                'currency' => 'gbp',
                'description' => "Fundraiser #{$fundraiserId} - {$data['reference']}",
                'payment_method_types' => ['card'],
                'customer' => $customer->id,
                'metadata' => [
                    'fundraiser_id' => $fundraiserId,
                    'reference' => $data['reference'],
                    'payer_user_id' => $user->id,
                    'linked_accounts' => implode(',', $linkedAccounts),
                ],
            ]);

            // 💳 Optionally confirm immediately if client sent payment method ID
            if ($request->filled('payment_method')) {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntent->id);
                $paymentIntent->confirm([
                    'payment_method' => $request->payment_method,
                ]);

                if ($paymentIntent->status === 'succeeded') {
                    // ✅ Record payment in your local DB
                    Payment::create([
                        'user_id'           => $user->id,
                        'payment_type'      => 'Fundraiser Payment',
                        'amount'            => $amount,
                        'reference'         => $data['reference'],
                        'fundraiser_id'     => $fundraiserId,
                        'membership_number' => $user->membership_number,
                        'verified'          => 1,
                        'image_name'        => "Online Stripe Payment {$paymentIntent->id}",
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Payment successful and recorded locally.',
                        'payment_id' => $paymentIntent->id,
                    ]);
                }
            }

            // If still awaiting confirmation (handled by client.js)
            return response()->json([
                'success' => true,
                'clientSecret' => $paymentIntent->client_secret,
                'message' => 'PaymentIntent created. Awaiting confirmation...',
            ]);

        } catch (\Throwable $e) {
            Log::error('Stripe createPaymentIntent error', [$e]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function createPaymentIntent__lates(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $user = auth()->user();

            // Validate input
            $data = $request->validate([
                'fundraiser_id'   => 'required|integer|exists:fundraisers,id',
                'linked_accounts' => 'nullable|string',
                'amount'          => 'required|numeric|min:1',
                'reference'       => 'nullable|string',
                'processed'       => 'nullable|boolean',
            ]);

            $amount = (float) $request->input('amount');
            $reference = $request->input('reference') ?? null;
            $fundraiserId = $request->input('fundraiser_id');
            $alreadyProcessed = $request->boolean('processed', false);

            $linkedAccounts = array_filter(explode(',', trim($request->input('linked_accounts') ?? '')));
            $hasLinked = !empty($linkedAccounts);

            Log::info("createPaymentIntent", $request->all());

            /**
             * 🔹 Create main Stripe customer
             */
            $mainCustomer = \Stripe\Customer::create([
                'email' => $user->email,
                'name'  => trim($user->first_name . ' ' . $user->last_name),
            ]);

            /**
             * 🔹 Create PaymentIntent for main payer
             */
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int) ($amount < 50 ? round($amount * 100) : round($amount)),
                'currency' => 'gbp',
                'description' => "Fundraiser #{$fundraiserId} - " . ($reference ?? 'Online Payment'),
                'payment_method_types' => ['card'],
                'customer' => $mainCustomer->id,
                'metadata' => [
                    'fundraiser_id' => $fundraiserId,
                    'reference' => $reference,
                    'payer_user_id' => $user->id,
                    'linked_accounts' => implode(',', $linkedAccounts),
                ],
            ]);

            /**
             * 🔹 Record main payment in local DB
             */
            Payment::create([
                'user_id'       => $user->id,
                'payment_type'  => 'Bereavement Fund',
                'amount'        => $amount,
                'reference'     => $reference,
                'fundraiser_id' => $fundraiserId,
                'membership_number' => $user->membership_number,
                'verified'      => 1,
                'image_name'    => "Online Stripe Payment {$paymentIntent->id}",
            ]);

            $invoices = [];
            $participants = collect([$user]);

            /**
             * 🔹 Pull linked users from DB
             */
            foreach ($linkedAccounts as $account) {
                $linkedUser = User::where('membership_number', trim($account))->first();
                if ($linkedUser) {
                    $participants->push($linkedUser);
                }
            }

            if ($participants->count() > 1) {
                $splitAmount = round($amount / $participants->count(), 2);

                Log::info("Creating {$participants->count()} invoices (including main user) for £{$splitAmount} each.");

                foreach ($participants as $p) {
                    Log::info("PARTICIPANT: ", [$p]);

                    try {
                        // Create Stripe customer for each participant
                        $customer = \Stripe\Customer::create([
                            'email' => $p->email,
                            'name' => trim($p->first_name . ' ' . $p->last_name),
                        ]);

                        if ($alreadyProcessed) {
                            // Mark invoice as paid directly
                            $invoice = \Stripe\Invoice::create([
                                'customer' => $customer->id,
                                'auto_advance' => false,
                                'collection_method' => 'send_invoice',
                                'description' => "Split payment already processed for Fundraiser #{$fundraiserId}",
                            ]);

                            $invoice->finalizeInvoice();
                            $invoice->pay(['paid_out_of_band' => true]);

                            $status = 'paid_out_of_band';
                            Log::info("Invoice {$invoice->id} marked paid for {$p->email}");
                        } else {
                            // Create invoice item
                            \Stripe\InvoiceItem::create([
                                'customer' => $customer->id,
                                'amount' => (int) round($splitAmount * 100),
                                'currency' => 'gbp',
                                'description' => "Split payment for Fundraiser #{$fundraiserId} ({$reference})",
                            ]);

                            // Create invoice (auto-charges customer)
                            $invoice = \Stripe\Invoice::create([
                                'customer' => $customer->id,
                                'collection_method' => 'charge_automatically',
                                'auto_advance' => true,
                                'description' => "Payment for {$reference}",
                            ]);

                            $invoice->finalizeInvoice();
                            $status = $invoice->status;

                            Log::info("Invoice {$invoice->id} created for {$p->email}");
                        }

                        // Record local Payment for participant
                        Payment::create([
                            'user_id'       => $p->id,
                            'payment_type'  => 'Bereavement Fund',
                            'amount'        => $splitAmount,
                            'reference'     => $reference,
                            'fundraiser_id' => $fundraiserId,
                            'membership_number' => $p->membership_number,
                            'verified'      => 1,
                            'image_name'    => "Stripe Invoice {$invoice->id} (marked paid)",
                        ]);

                        $invoices[] = [
                            'id' => $invoice->id,
                            'email' => $p->email,
                            'amount' => $splitAmount,
                            'status' => $status,
                            'url' => $invoice->hosted_invoice_url ?? null,
                        ];
                    } catch (\Exception $e) {
                        Log::error("Error creating invoice for {$p->email}: " . $e->getMessage());
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment_intent' => $paymentIntent->id,
                'split_amount' => $splitAmount ?? $amount,
                'invoices' => $invoices,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Stripe createPaymentIntent error', [$e]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function __createPaymentIntent(Request $request)
    {
    try {
        Stripe::setApiKey(config('services.stripe.secret'));

        $user = auth()->user();

        $data = $request->validate([
            'fundraiser_id'   => 'required|integer|exists:fundraisers,id',
            'linked_accounts' => 'nullable|string',
            'amount'          => 'required|numeric|min:1',
            'reference'       => 'nullable|string',
        ]);

        $amount = (float) $data['amount'];
        $linkedAccounts = array_filter(explode(',', trim($data['linked_accounts'] ?? '')));
        $fundraiserId = $data['fundraiser_id'];

        $mainCustomer = \Stripe\Customer::create([
            'email' => $user->email,
            'name'  => trim($user->first_name . ' ' . $user->last_name),
        ]);

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => (int) round($amount * 100),
            'currency' => 'gbp',
            'description' => "Fundraiser #{$fundraiserId} - {$data['reference']}",
            'payment_method_types' => ['card'],
            'customer' => $mainCustomer->id,
            'metadata' => [
                'fundraiser_id' => $fundraiserId,
                'reference' => $data['reference'],
                'payer_user_id' => $user->id,
                'linked_accounts' => implode(',', $linkedAccounts),
            ],
        ]);

        return response()->json([
            'success' => true,
            'clientSecret' => $paymentIntent->client_secret,
            'message' => 'PaymentIntent created. Awaiting confirmation...',
        ]);

    } catch (\Throwable $e) {
        Log::error('Stripe createPaymentIntent error', [$e]);
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}


    // /**
    //  * Create a PaymentIntent
    //  */
    // public function createPaymentIntent(Request $request)
    // {
    //     Log::info("createPaymentIntent");
    //     Log::info("createIntent", [$request->all()]);

    //     $user = auth()->user();

    //     // Create a Stripe customer
    //     $customer = Customer::create([
    //         'email' => $user->email,
    //         'name'  => $user->first_name . " ". $user->last,
    //     ]);

    //     $amount = $request->input('amount');

    //      // validate request
    //     $data = $request->validate([
    //         'fundraiser_id'   => 'required|integer|exists:fundraisers,id',
    //         'linked_accounts' => 'nullable|string',
    //     ]);

    //     $linked_accounts = explode(",", trim($request->input('linked_accounts')));

    //     $comparator = count($linked_accounts) * doubleval(setting('default_contribution'));
    //     $value = doubleval(setting('default_contribution'));

    //     if ($value < $comparator) {
    //         return redirect()->back()
    //                         ->withInput()
    //                         ->with('custom_message', 'Invoice amount does not correspond with the number of Members paying for.');
    //     }


    //     $paymentIntent = PaymentIntent::create([
    //         'amount'   => $amount,
    //         'description' => trim($request->input('fundraiser_id')) . " - " . trim($request->input('reference')),
    //         'currency' => 'gbp',
    //         'payment_method_types' => ['card'],
    //         'customer' => $customer->id,
    //         'metadata' => [
    //             'Fundraiser Id'   => $request->fundraiser_id,
    //             'Reference'       => $request->reference
    //         ]
    //     ]);

    //     if ($linked_accounts[0] !== "") {
    //         foreach ($linked_accounts as $account) {
    //             $userId = User::where('membership_number', $account)->first();
    //             // check total of invoice, if total covers linked accounts then verify as paid for the other members.
    //             if ($userId) {
    //                 $payment = new Payment();
    //                 $payment->user_id = $userId->id;
    //                 $payment->payment_type = 'Bereavement Fund';
    //                 $payment->amount = $request->amount;
    //                 $payment->reference = $request->reference;
    //                 $payment->fundraiser_id = $request->fundraiser_id;
    //                 $payment->verified = 1;
    //                 $payment->image_name = "Online Stripe Payment {{ $paymentIntent->id }}";
    //                 $payment->save();
    //             }
    //         }
    //     } else {
    //         // check total of invoice, if total covers linked accounts then verify as paid for the other members.
    //         $payment = new Payment();
    //         $payment->user_id = auth()->id();
    //         $payment->payment_type = 'Bereavement Fund';
    //         $payment->amount = $request->amount;
    //         $payment->reference = $request->reference;
    //         $payment->verified = 1;
    //         $payment->fundraiser_id = $request->fundraiser_id;
    //         $payment->image_name = "Online Stripe Payment {{ $paymentIntent->id }}";
    //         $payment->save();
    //     }

    //     return response()->json([
    //         'clientSecret' => $paymentIntent->client_secret,
    //     ]);
    // }

    public function createIntent(Request $request)
    {
        Log::info("createIntent", [$request->all()]);

        // validate request
        $data = $request->validate([
            'fundraiser_id'   => 'required|integer|exists:fundraisers,id',
            'linked_accounts' => 'nullable|string',
        ]);

        $linked_accounts = explode(",", trim($request->input('linked_accounts')));

        $comparator = count($linked_accounts) * doubleval(setting('default_contribution'));
        $value = doubleval(setting('default_contribution'));

        if ($value < $comparator) {
            return redirect()->back()
                            ->withInput()
                            ->with('custom_message', 'Invoice amount does not correspond with the number of Members paying for.');
        }

        if ($linked_accounts[0] !== "") {
            foreach ($linked_accounts as $account) {
                $userId = User::where('membership_number', $account)->first();
                // check total of invoice, if total covers linked accounts then verify as paid for the other members.
                if ($userId) {
                    $payment = new Payment();
                    $payment->user_id = $userId->id;
                    $payment->payment_type = 'Bereavement Fund';
                    $payment->amount = $request->amount;
                    $payment->reference = $request->reference;
                    $payment->fundraiser_id = $request->fundraiser_id;
                    $payment->verified = 1;
                    $payment->save();
                }
            }
        } else {
            // check total of invoice, if total covers linked accounts then verify as paid for the other members.
            $payment = new Payment();
            $payment->user_id = auth()->id();
            $payment->payment_type = 'Bereavement Fund';
            $payment->amount = $request->amount;
            $payment->reference = $request->reference;
            $payment->verified = 1;
            $payment->fundraiser_id = $request->fundraiser_id;
            $payment->save();
        }

        // get fundraiser
        $fundraiser = Fundraiser::findOrFail($data['fundraiser_id']);

        // base amounts
        $baseAmountDollars = $fundraiser->amount ?? 0;
        $baseAmountCents   = (int) round($baseAmountDollars * 100);

        // calculate multiplier
        $multiplier = 1;
        if (!empty($data['linked_accounts'])) {
            $accounts = collect(explode(',', $data['linked_accounts']))
                        ->map(fn($a) => trim($a))
                        ->filter(fn($a) => $a !== '');
            $multiplier = $accounts->count() + 1; // bereaved + linked
        }

        // final amount
        $finalAmountCents = $baseAmountCents * $multiplier;

        // create PaymentIntent with Stripe
        // Stripe::setApiKey(config('services.stripe.secret'));

        $intent = PaymentIntent::create([
            'amount' => $finalAmountCents,
            'currency' => 'usd', // or "gbp" if UK-based
            'metadata' => [
                'fundraiser_id'   => $fundraiser->id,
                'linked_accounts' => $data['linked_accounts'] ?? '',
                'user_id'         => $request->user()->id,
            ]
        ]);

        return response()->json([
            'clientSecret' => $intent->client_secret,
            'amount'       => $finalAmountCents,
        ]);
    }

    /**
     * Handle Stripe Webhooks
     */
    public function __handleWebhook(Request $request)
    {
        $event = null;

        Log::info("message", $request->all());

        try {
            $event = Event::constructFrom($request->all());
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handlePaymentIntentSucceeded($paymentIntent);
                break;
            default:
                Log::info('Unhandled event type: ' . $event->type);
        }

        return response()->json(['status' => 'success']);
    }

    public function handleWebhook(Request $request)
    {
        try {
            $event = \Stripe\Event::constructFrom($request->all());
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;
            default:
                Log::info('Unhandled event type: ' . $event->type);
        }

        return response()->json(['status' => 'success']);
    }

    public function handleStripeWebhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $event = $request->getContent();
        $payload = json_decode($event, true);
        $eventType = $payload['type'] ?? '';

        if ($eventType === 'payment_intent.succeeded') {
            $intent = $payload['data']['object'];

            $userId = $intent['metadata']['payer_user_id'] ?? null;
            $fundraiserId = $intent['metadata']['fundraiser_id'] ?? null;
            $reference = $intent['metadata']['reference'] ?? null;
            $amount = $intent['amount_received'] / 100; // Convert back to GBP

            if ($userId && $fundraiserId) {
                Payment::updateOrCreate(
                    ['reference' => $reference, 'fundraiser_id' => $fundraiserId],
                    [
                        'user_id'           => $userId,
                        'payment_type'      => 'Fundraiser Payment',
                        'amount'            => $amount,
                        'verified'          => 1,
                        'image_name'        => "Stripe Payment {$intent['id']}",
                    ]
                );

                Log::info("Payment recorded for User {$userId}, Fundraiser {$fundraiserId}");
            }
        }

        return response()->json(['status' => 'success']);
    }

    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        Log::info("✅ Payment succeeded: {$paymentIntent->id}");

        $metadata = $paymentIntent->metadata;
        $payer = User::find($metadata->payer_user_id);
        if (!$payer) {
            Log::warning("Payer not found for payment intent {$paymentIntent->id}");
            return;
        }

        $fundraiserId = $metadata->fundraiser_id;
        $reference = $metadata->reference;
        $linkedAccounts = array_filter(explode(',', trim($metadata->linked_accounts ?? '')));
        $participants = collect([$payer]);

        foreach ($linkedAccounts as $account) {
            $linkedUser = User::where('membership_number', trim($account))->first();
            if ($linkedUser) $participants->push($linkedUser);
        }

        $splitAmount = round(($paymentIntent->amount_received / 100) / $participants->count(), 2);

        foreach ($participants as $p) {
            try {
                $customer = \Stripe\Customer::create([
                    'email' => $p->email,
                    'name'  => "{$p->first_name} {$p->last_name}",
                ]);

                \Stripe\InvoiceItem::create([
                    'customer' => $customer->id,
                    'amount'   => (int) round($splitAmount * 100),
                    'currency' => 'gbp',
                    'description' => "Payment share for Fundraiser #{$fundraiserId} ({$reference})",
                ]);

                $invoice = \Stripe\Invoice::create([
                    'customer' => $customer->id,
                    'collection_method' => 'send_invoice',
                    'auto_advance' => false,
                    'description' => "Confirmed payment for Fundraiser #{$fundraiserId}",
                ]);

                $invoice->finalizeInvoice();
                $invoice->pay(['paid_out_of_band' => true]);

                Log::info("✅ Invoice {$invoice->id} created & marked paid for {$p->email}");

                Payment::updateOrCreate(
                    [
                        'fundraiser_id' => $fundraiserId,
                        'user_id' => $p->id,
                        'reference' => $reference,
                    ],
                    [
                        'payment_type'  => 'Bereavement Fund',
                        'amount'        => $splitAmount,
                        'verified'      => 1,
                        'image_name'    => "Stripe Invoice {$invoice->id} (paid)",
                    ]
                );

            } catch (\Exception $e) {
                Log::error("❌ Failed creating invoice for {$p->email}: " . $e->getMessage());
            }
        }
    }

    protected function __handlePaymentIntentSucceeded($paymentIntent)
    {
        Log::info('Payment succeededxxxxx: ' . $paymentIntent->id);

        // // Example: mark invoice paid if attached
        // if ($paymentIntent->metadata->invoice_id ?? false) {
        //     Invoice::where('id', $paymentIntent->metadata->invoice_id)
        //         ->update(['paid' => true]);
        // }

        // // Optionally send confirmation email
        // if (!empty($paymentIntent->receipt_email)) {
        //     Mail::to($paymentIntent->receipt_email)
        //         ->queue(new PaymentConfirmation($paymentIntent));
        // }
    }

    /**
     * Payment Success page
     */
    public function __paymentSuccess(Request $request)
    {

        Log::info("Payment", [$request->all()]);

        return view('payments.success', [
            'paymentIntent' => $request->input('paymentIntent')
        ]);
    }



    public function paymentSuccessXXX(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntentId = $request->get('paymentIntent');
            $amount          = (float) $request->get('amount');
            $fundraiserId    = $request->get('fundraiser_id');
            $linkedAccounts  = array_filter(explode(',', $request->get('linked_accounts') ?? ''));
            $reference       = $request->get('reference');
            $user            = auth()->user();

            // 🔹 Retrieve the payment intent from Stripe
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status !== 'succeeded') {
                return redirect()->route('payment.failed')->with('error', 'Payment not confirmed.');
            }

            /**
             * 🔹 If there are linked accounts, split the payment equally
             */
            if (!empty($linkedAccounts)) {
                $splitAmount = $amount / (count($linkedAccounts) + 1); // include main user share

                // Create payment for main user
                Payment::create([
                    'user_id'           => $user->id,
                    'payment_type'      => 'Bereavement Fund',
                    'amount'            => $splitAmount,
                    'reference'         => $reference,
                    'fundraiser_id'     => $fundraiserId,
                    'membership_number' => $user->membership_number,
                    'verified'          => 1,
                    'image_name'        => "Online Stripe Payment {$paymentIntent->id}",
                ]);

                // Create payments for linked accounts
                foreach ($linkedAccounts as $linkedUserId) {
                    Payment::create([
                        'user_id'           => $linkedUserId,
                        'payment_type'      => 'Bereavement Fund (Linked)',
                        'amount'            => $splitAmount,
                        'reference'         => $reference,
                        'fundraiser_id'     => $fundraiserId,
                        'membership_number' => 'LINKED-' . $linkedUserId,
                        'verified'          => 1,
                        'image_name'        => "Online Split Stripe Payment {$paymentIntent->id}",
                    ]);
                }
            } else {
                /**
                 * 🔹 No linked accounts – record single payment
                 */
                Payment::create([
                    'user_id'           => $user->id,
                    'payment_type'      => 'Bereavement Fund',
                    'amount'            => $amount,
                    'reference'         => $reference,
                    'fundraiser_id'     => $fundraiserId,
                    'membership_number' => $user->membership_number,
                    'verified'          => 1,
                    'image_name'        => "Online Stripe Payment {$paymentIntent->id}",
                ]);
            }

            return view('payments.success', [
                'paymentIntent' => $paymentIntent,
                'amount' => $amount,
                'fundraiserId' => $fundraiserId,
                'linkedAccounts' => $linkedAccounts,
            ]);

        } catch (\Throwable $e) {
            Log::error('Payment Success Error', ['error' => $e->getMessage()]);
            return redirect()->route('payment.failed')->with('error', $e->getMessage());
        }
    }


    public function paymentSuccess(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntentId = $request->get('paymentIntent');
            $amount          = (float) $request->get('amount');
            $fundraiserId    = $request->get('fundraiser_id');
            $linkedAccounts  = array_filter(explode(',', $request->get('linked_accounts') ?? ''));
            $reference       = $request->get('reference');
            $user            = auth()->user();

            // 🔹 Retrieve and verify the Stripe payment
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
            if ($paymentIntent->status !== 'succeeded') {
                return redirect()->route('payment.failed')->with('error', 'Payment not confirmed.');
            }

            // 🔹 Determine split (main user + linked accounts)
            $splitCount = max(count($linkedAccounts) + 1, 1);
            $splitAmount = round($amount / $splitCount, 2);

            // 🔹 Record main user's payment
            Payment::create([
                'user_id'           => $user->id,
                'payment_type'      => 'Bereavement Fund',
                'amount'            => $splitAmount,
                'reference'         => $reference,
                'fundraiser_id'     => $fundraiserId,
                'membership_number' => $user->membership_number,
                'verified'          => 1,
                'image_name'        => "Online Stripe Payment {$paymentIntent->id}",
            ]);

            // 🔹 Record linked accounts if any
            foreach ($linkedAccounts as $linked) {
                $linkedUser = null;

                // // Try to find user by email or ID
                // if (is_numeric($linked)) {
                // } else {
                //     $linkedUser = User::where('email', trim($linked))->first();
                // }

                $linkedUser = User::where('membership_number', trim($linked))->first();

                if ($linkedUser) {
                    Payment::create([
                        'user_id'           => $linkedUser->id,
                        'payment_type'      => 'Bereavement Fund (Linked)',
                        'amount'            => $splitAmount,
                        'reference'         => $reference,
                        'fundraiser_id'     => $fundraiserId,
                        'membership_number' => $linkedUser->membership_number,
                        'verified'          => 1,
                        'image_name'        => "Online Split Stripe Payment {$paymentIntent->id}",
                    ]);
                } else {
                    // If linked user not found, record a placeholder
                    Payment::create([
                        'user_id'           => null,
                        'payment_type'      => 'Bereavement Fund (Linked - Unknown)',
                        'amount'            => $splitAmount,
                        'reference'         => $reference,
                        'fundraiser_id'     => $fundraiserId,
                        'membership_number' => 'UNKNOWN',
                        'verified'          => 1,
                        'image_name'        => "Online Split Stripe Payment {$paymentIntent->id}",
                    ]);

                    Log::warning("Linked account '{$linked}' not found for payment split.");
                }
            }

            return view('payments.success', [
                'paymentIntent'   => $paymentIntent,
                'amount'          => $amount,
                'fundraiserId'    => $fundraiserId,
                'linkedAccounts'  => $linkedAccounts,
                'splitAmount'     => $splitAmount,
            ]);

        } catch (\Throwable $e) {
            Log::error('Payment Success Error', ['error' => $e->getMessage()]);
            return redirect()->route('payments.failed')->with('error', $e->getMessage());
        }
    }
}
