@extends('layouts.auth')

@section('page-title', __('Sign Up'))

@if (setting('registration.captcha.enabled'))
    <script src='https://www.google.com/recaptcha/api.js'></script>
@endif

@section('content')


    <div class="col-md-8 mx-auto my-10p">
        <div class="text-center">
            {{-- <x-logo /> --}}
        </div>

        <div class="card mt-5">
            <div class="card-body">
                <h5 class="card-title text-center mt-4 text-uppercase">
                    @lang('Register')
                </h5>

                <div class="p-4">
                    {{-- @include('auth.social.buttons') --}}

                    @include('partials/messages')

                    <form role="form" action="<?= url('register') ?>" method="post" id="registration-form" autocomplete="off" class="register-page__form" enctype="multipart/form-data">
                        <input type="hidden" value="<?= csrf_token() ?>" name="_token">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="form-group">
                                    <input type="text"
                                            name="first_name"
                                            id="first_name"
                                            class="form-control input-solid"
                                            placeholder="@lang('First Name')"
                                            value="{{ old('first_name') }}"
                                            required>
                                </div>

                                <div class="form-group">
                                    <input type="text"
                                            name="last_name"
                                            id="last_name"
                                            class="form-control input-solid"
                                            placeholder="@lang('Last Name')"
                                            value="{{ old('last_name') }}"
                                            required>
                                </div>

                                <div class="form-group">
                                    <input type="email"
                                            name="email"
                                            id="email"
                                            class="form-control input-solid"
                                            placeholder="@lang('Email')"
                                            value="{{ old('email') }}"
                                            required>
                                </div>

                                <div class="form-group">
                                    <input type="text"
                                            name="phone"
                                            id="phone"
                                            class="form-control input-solid"
                                            placeholder="@lang('Phone Number')"
                                            value="{{ old('phone') }}">
                                </div>

                                <div class="form-group">
                                    <label for="image">Enter your Date of Birth</label>
                                    <input type="date"
                                            name="birthday"
                                            id="birthday"
                                            class="form-control input-solid"
                                            placeholder="@lang('Date of Birth e.g 1980-04-18')"
                                            value="{{ old('birthday') }}"
                                            required>
                                </div>

                                <div class="form-group">
                                    <label for="image">Region</label>
                                    <select class="form-control input-solid" name="region" aria-invalid="false" required>
                                        <option>Select your Region</option>
                                        <option value="LN">London</option>
                                        <option value="SE">South East England</option>
                                        <option value="SW">South West England</option>
                                        <option value="EE">East of England</option>
                                        <option value="NE">North East of England</option>
                                        <option value="WM">West Midlands</option>
                                        <option value="EM">East Midlands</option>
                                        <option value="YH">Yorkshire and the Humber</option>
                                        <option value="NW">North West England</option>
                                        <option value="WL">Wales</option>
                                        <option value="NI">Northern Ireland</option>
                                        <option value="SC">Scotland</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <input type="password"
                                    name="password"
                                    id="password"
                                    class="form-control input-solid"
                                    placeholder="@lang('Password')"
                                    required>
                                </div>

                                <div class="form-group">
                                    <input type="password"
                                            name="password_confirmation"
                                            id="password_confirmation"
                                            class="form-control input-solid"
                                            placeholder="@lang('Confirm Password')">
                                </div>


                                <!-- Contribution Amount -->
                                <div class="form-group my-4">
                                    <label for="amount">@lang('Registration Fee')</label>
                                    <input type="number" step="0.01" id="amount" name="amount"
                                        class="form-control input-solid"
                                        value="10">
                                        {{-- value="{{ number_format((float) $unpaidFundraisers->first()->amount, 2, '.', '') ?? 0 }}"> --}}

                                </div>

                                <hr>

                                <!-- Cardholder Name -->
                                <div class="mb-3">
                                    <label for="cardholder-name" class="form-label">Cardholder Name!</label>
                                    <input type="text" id="cardholder-name" class="form-control input-solid" required>
                                </div>

                                <!-- Postcode -->
                                <div class="mb-3">
                                    <label for="postcode" class="form-label">Postcode</label>
                                    <input type="text" id="postcode" class="form-control input-solid" required>
                                </div>

                                <!-- Card Element -->
                                <div class="mb-3">
                                    <label for="card-element" class="form-label">Card Details</label>
                                    <div id="card-element" class="form-control input-solid"></div>
                                </div>

                                {{-- <!-- Pay Button -->
                                <button id="submit" class="btn btn-primary mt-3 w-100">
                                    Pay Now
                                </button> --}}

                                @if (setting('tos'))

                                    <div class="form-group">
                                        <input type="checkbox" class="custom-control-input" name="tos" id="tos" value="1" required/>
                                        <label class="custom-control-label font-weight-normal" for="tos">
                                            @lang('I accept')
                                            <a href="#tos-modal" data-toggle="modal">@lang('Terms of Service')</a>
                                        </label>
                                    </div>

                                @endif

                                {{-- Only display captcha if it is enabled --}}
                                @if (setting('registration.captcha.enabled'))
                                    <div class="form-group my-4">
                                        {!! app('captcha')->display() !!}
                                    </div>
                                @endif
                                {{-- end captcha --}}

                                <!-- Pay Button -->
                                <div class="form-group mt-4">
                                    <button id="submit" class="btn btn-primary mt-3 w-100">
                                        Pay Now
                                    </button>
                                    {{-- <button type="submit" class="btn btn-primary btn-lg btn-block careox-btn" id="btn-login" > --}}
                                        {{-- onclick="this.innerHTML = 'Processing ...!'" --}}
                                        {{-- @lang('Register') --}}
                                    {{-- </button> --}}
                                </div>

                            </div>

                        </div>



                        {{-- <button type="submit" class="careox-btn"><span>Register Now</span></button> --}}
                    </form>

                </div>
            </div>
        </div>

        <div class="text-center text-muted">
            @if (setting('reg_enabled'))
                @lang('Already have an account?')
                <a class="font-weight-bold" href="<?= url("login") ?>">@lang('Login')</a>
            @endif
        </div>

    </div>

    @if (setting('tos'))
        <div class="modal fade" id="tos-modal" tabindex="-1" role="dialog" aria-labelledby="tos-label">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tos-label">@lang('Terms of Service')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @include('auth.tos')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            @lang('Close')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@stop

@section('scripts')
    {!! JsValidator::formRequest('App\Http\Requests\Auth\RegisterRequest', '#registration-form') !!}
@stop


{{-- @php
    $amountDollars = $unpaidFundraisers->first()->amount ?? 0;
    $amountCents = (int) round($amountDollars * 100);
@endphp --}}

<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const stripe = Stripe("{{ config('services.stripe.key') }}");
    const form = document.getElementById("payment-form");
    const submitButton = document.getElementById("submit");
    const linkedAccountsInput = document.getElementById("linked_accounts");
    const amountInput = document.getElementById("amount");
    const BASE_AMOUNT = parseFloat(amountInput.value) || 0;

    const elements = stripe.elements();
    const cardElement = elements.create("card", { hidePostalCode: true });
    cardElement.mount("#card-element");

    linkedAccountsInput.addEventListener("input", () => {
        const accounts = linkedAccountsInput.value.split(",")
            .map(a => a.trim())
            .filter(a => a.length > 0);

        const total = BASE_AMOUNT * (1 + accounts.length);
        amountInput.value = total.toFixed(2);
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        submitButton.disabled = true;
        submitButton.textContent = "Processing...";

        const cardholderName = document.getElementById("cardholder-name").value.trim();
        const postcode = document.getElementById("postcode").value.trim();
        const amount = parseFloat(amountInput.value);

        if (!cardholderName || !postcode || !amount) {
            alert("Please complete all required fields.");
            submitButton.disabled = false;
            submitButton.textContent = "Pay Now";
            return;
        }

        const formData = new FormData(form);
        let bodyData = {};
        formData.forEach((value, key) => { bodyData[key] = value; });

        // ✅ Send amount in dollars (not cents)
        bodyData.amount = parseFloat(amount.toFixed(2));
        bodyData.linked_accounts = linkedAccountsInput.value.trim();

        try {
            const res = await fetch("{{ route('payment.intent') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(bodyData)
            });

            const data = await res.json();
            console.log("Stripe response:", data);

            if (data.clientSecret) {
                const { error, paymentIntent } = await stripe.confirmCardPayment(data.clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: cardholderName,
                            address: { postal_code: postcode }
                        }
                    }
                });

                if (error) {
                    alert("Payment failed: " + error.message);
                    submitButton.disabled = false;
                    submitButton.textContent = "Pay Now";
                    return;
                }

                // if (paymentIntent && paymentIntent.status === "succeeded") {
                //     window.location.href = "{{ route('payment.success') }}?paymentIntent=" + paymentIntent.id;
                // }

                if (paymentIntent && paymentIntent.status === "succeeded") {
                    // Get the form element and form data
                    const form = document.getElementById('payment-form');
                    const formData = new FormData(form);

                    // Build query string from form data
                    const params = new URLSearchParams();
                    formData.forEach((value, key) => {
                        params.append(key, value);
                    });

                    // Add Stripe paymentIntent ID
                    params.append('paymentIntent', paymentIntent.id);

                    // Redirect to Laravel success route with all form data
                    window.location.href = "{{ route('payment.success') }}?" + params.toString();
                }

            } else if (data.success) { // ✅ Updated condition
                alert(data.message || "Invoices created and marked as paid successfully.");
                window.location.href = "{{ route('payment.success') }}";
            } else {
                throw new Error("Unexpected response from server.");
            }

        } catch (err) {
            console.error("Payment error:", err);
            alert("An unexpected error occurred: " + err.message);
            submitButton.disabled = false;
            submitButton.textContent = "Pay Now";
        }
    });
});
</script>
