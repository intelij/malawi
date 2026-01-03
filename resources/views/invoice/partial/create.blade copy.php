

 <div class="col-md-4">
    <div class="card">
        <h6 class="card-header">
            @lang('Pay Outstanding Invoice')
            <small style="float: right;">
                <a href="#"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="@lang('You have outstanding payments')">
                    <strong>Outstanding: </strong> {{ $totalFundraisers }} invoice(s)
                </a>
            </small>
        </h6>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="payment-form">
                @csrf

                <!-- Bereaved Member Reference -->
                <div class="form-group">
                    <label for="bereaved_ref_number">
                        @lang('Bereaved Member')
                        <br><small class="text-muted">
                            @lang('This is the reference number of the bereaved member.')
                        </small>
                    </label>
                    <input type="text" class="form-control input-solid"
                        value="{{ optional($latestFundraiser->user)->membership_number }} - {{ optional($latestFundraiser->user)->first_name }} {{ optional($latestFundraiser->user)->last_name }}"
                        disabled>
                    <input type="hidden" name="reference" value="{{ optional($latestFundraiser->user)->membership_number }}">
                    <input type="hidden" name="fundraiser_id" value="{{ $latestFundraiser->id }}">
                </div>

                <!-- Payment Type -->
                <div class="form-group my-4">
                    <label>@lang('Payment Type')</label>
                    <input type="hidden" name="payment_type" value="Bereavement Fund">
                    <input type="text" class="form-control input-solid" value="Bereavement Fund" disabled>
                </div>

                <!-- Linked Accounts -->
                <div class="form-group my-4">
                    <label for="linked_accounts">@lang('Who Else Did You Pay For?')<br/>
                        <span style="font-weight: 100;">
                            Add membership numbers separated by commas, if paying for multiple members.
                        </span>
                    </label>
                    <input type="text" class="form-control input-solid" id="linked_accounts"
                        name="linked_accounts" placeholder="PAM0012,PAM08374,PAM04234">
                </div>

                {{-- <!-- Upload Proof -->
                <div class="form-group my-4">
                    <label for="invoice">Upload proof of payment as an image (Max size: 2MB)</label>
                    <input type="file" name="invoice" id="invoice" class="form-control-file form-control input-solid">
                </div> --}}

                <!-- Contribution Amount -->
                <div class="form-group my-4">
                    <label for="amount">@lang('Contribution Amount')</label>
                    <input type="number" step="0.01" id="amount" name="amount"
                        class="form-control input-solid"
                        value="{{ number_format((float) $unpaidFundraisers->first()->amount, 2, '.', '') ?? 0 }}">
                </div>

                <hr>

                <!-- Cardholder Name -->
                <div class="mb-3">
                    <label for="cardholder-name" class="form-label">Cardholder Name</label>
                    <input type="text" id="cardholder-name" class="form-control" required>
                </div>

                <!-- Postcode -->
                <div class="mb-3">
                    <label for="postcode" class="form-label">Postcode</label>
                    <input type="text" id="postcode" class="form-control" required>
                </div>

                <!-- Card Element -->
                <div class="mb-3">
                    <label for="card-element" class="form-label">Card Details</label>
                    <div id="card-element" class="form-control"></div>
                </div>

                <!-- Pay Button -->
                <button id="submit" class="btn btn-primary mt-3 w-100">
                    Pay Now
                </button>
            </form>

            <div id="payment-message" class="mt-3 text-success" style="display:none;"></div>
        </div>
        <div id="payment-message" class="mt-3 text-success" style="display:none;"></div>
    </div>
</div>

<div class="col-md-8">
        <div class="card">
            <h6 class="card-header d-flex align-items-center justify-content-between">
                @lang('Outstanding Invoices')
            </h6>
            <div class="card-body">

                <div class="table-responsive" id="users-table-wrapper">
                    <table class="table table-borderless table-striped">
                        <thead>
                        <tr>
                            <th class="min-width-80">@lang('Proof')</th>
                            <th class="min-width-150">@lang('Amount')</th>
                            <th class="min-width-100">@lang('Type')</th>
                            <th class="min-width-100">@lang('Reference')</th>
                            <th class="min-width-80">@lang('Authorised By')</th>
                            <th class="min-width-80">@lang('Status')</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach ($unpaidFundraisers as $payment)
                            <tr>
                                <td class="align-middle">
                                    @if (isset($payment->payment_type) && $payment->payment_type == "Bereaved")
                                        <img src="/upload/invoices/8234.jpg" width="100"/>
                                    @else
                                        <img src="/upload/invoices/{{ $payment->image_name }}" width="100"/>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $payment->amount ?: __('N/A') }} </td>
                                <td class="align-middle">{{ $payment->payment_type ?: __('N/A') }} </td>
                                {{-- <td class="align-middle">{{ $payment->reference ?: __(' - ') }}</td> --}}
                                <td class="align-middle">{{ $payment->reference ?: optional($payment->user)->membership_number }}</td>
                                <td class="align-middle">
                                    @if (isset($payment->authorised_by) && $payment->authorised_by)
                                        <span class="badge badge-lg badge-success">
                                            @if ($payment->authorised_by <= 9)
                                                PAM000{{ $payment->authorised_by }}
                                            @elseif ($payment->authorised_by >= 10 && $payment->authorised_by <= 99)
                                                PAM00{{ $payment->authorised_by }}
                                            @elseif ($payment->authorised_by >= 100 && $payment->authorised_by <= 999)
                                                PAM0{{ $payment->authorised_by }}
                                            @else
                                                PAM{{ $payment->authorised_by }}
                                            @endif
                                        </span>
                                    @else
                                        <span class="badge badge-lg badge-warning">
                                            Unassigned
                                        </span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if (isset($payment->verified) && $payment->verified)
                                        <span class="badge badge-lg badge-success">
                                            Verified
                                        </span>
                                    @elseif (isset($payment->payment_type) && $payment->payment_type == "Bereaved")
                                        <span class="badge badge-lg badge-success">
                                            Verified
                                        </span>
                                    @else
                                        <span class="badge badge-lg badge-warning">
                                            Pending Verification
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>


@php
    $amountDollars = $unpaidFundraisers->first()->amount ?? 0;
    $amountCents = (int) round($amountDollars * 100);
@endphp

<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const stripe = Stripe("{{ config('services.stripe.key') }}");
    const form = document.getElementById("payment-form");
    const submitButton = document.getElementById("submit");
    const amountInput = document.getElementById("amount");
    const linkedAccountsInput = document.getElementById("linked_accounts");

    let cardElement;

    const elements = stripe.elements();
    cardElement = elements.create("card", { hidePostalCode: true });
    cardElement.mount("#card-element");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        submitButton.disabled = true;
        submitButton.textContent = "Processing...";

        const fundraiserId = document.getElementById("fundraiser_id").value;
        const reference = document.getElementById("reference").value;
        const amount = parseFloat(amountInput.value);
        const linked_accounts = linkedAccountsInput.value.trim();

        if (!fundraiserId || !amount) {
            alert("Please provide all required details.");
            submitButton.disabled = false;
            submitButton.textContent = "Pay Now";
            return;
        }

        // 🔹 Step 1: Create PaymentIntent(s) on backend
        const createRes = await fetch("{{ route('payment.intent') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
            },
            body: JSON.stringify({
                fundraiser_id: fundraiserId,
                reference,
                amount,
                linked_accounts,
            }),
        });

        const createData = await createRes.json();

        if (!createData.success) {
            alert("Error creating payment: " + createData.message);
            submitButton.disabled = false;
            submitButton.textContent = "Pay Now";
            return;
        }

        // 🔹 Step 2: Confirm each PaymentIntent
        for (const payment of createData.payments) {
            const { client_secret, intent_id, email, split_amount } = payment;

            const { error, paymentIntent } = await stripe.confirmCardPayment(client_secret, {
                payment_method: {
                    card: cardElement,
                    billing_details: {
                        name: "{{ auth()->user()->name ?? 'Donor' }}",
                        email,
                    },
                },
            });

            if (error) {
                console.error("Stripe error:", error);
                alert(error.message);
                submitButton.disabled = false;
                submitButton.textContent = "Pay Now";
                return;
            }

            // 🔹 Step 3: Confirm with backend (record local DB payment)
            const confirmRes = await fetch("{{ route('payment.confirm') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                },
                body: JSON.stringify({ payment_intent_id: paymentIntent.id }),
            });

            const confirmData = await confirmRes.json();

            if (confirmData.success) {
                console.log(`✅ Payment for ${email} recorded:`, confirmData.message);
            } else {
                console.warn(`⚠️ Could not record payment for ${email}:`, confirmData.message);
            }
        }

        // 🔹 Step 4: Redirect or show success message
        alert("🎉 Payment successful and recorded locally!");
        window.location.href = "{{ route('payment.success') }}";
    });
});
</script>

