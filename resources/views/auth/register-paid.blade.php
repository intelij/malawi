@extends('layouts.auth')

@section('page-title', __('Sign Up'))

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="stripe-key" content="{{ config('services.stripe.key') }}">

<div class="col-md-8 mx-auto my-10p">
    <div class="card mt-5">
        <div class="card-body">

            <h5 class="card-title text-center mt-4 text-uppercase">
                @lang('Register')
            </h5>

            @include('partials/messages')

            <form id="registration-form"
                  method="POST"
                  action="{{ route('register.complete') }}"
                  data-intent-url="{{ route('pay.intent') }}"
                  autocomplete="off">

                @csrf

                <input type="hidden" name="payment_intent_id" id="payment_intent_id">

                {{-- USER DETAILS --}}
                <input type="text" name="first_name" class="form-control input-solid mb-3"
                       placeholder="First Name" required>

                <input type="text" name="last_name" class="form-control input-solid mb-3"
                       placeholder="Last Name" required>

                <input type="email" name="email" class="form-control input-solid mb-3"
                       placeholder="Email" required>

                <input type="password" name="password" class="form-control input-solid mb-3"
                       placeholder="Password" required>

                <input type="password" name="password_confirmation"
                       class="form-control input-solid mb-3"
                       placeholder="Confirm Password" required>

                <hr>

                {{-- PAYMENT --}}
                <label>Registration Fee</label>
                <input type="number" name="amount" id="amount"
                       class="form-control input-solid mb-3"
                       value="7.20" readonly>

                <input type="text" id="cardholder-name"
                       class="form-control input-solid mb-3"
                       name="cardholder-name"
                       placeholder="Cardholder Name" required>

                <input type="text" id="postcode"
                       class="form-control input-solid mb-3"
                       name="postcode"
                       placeholder="Postcode" required>

                <div id="card-element" class="form-control input-solid mb-4"></div>

                <button id="submit" class="btn btn-primary w-100">
                    Pay & Register
                </button>

            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script src="{{ asset('js/register-payment.js') }}" defer></script>
@endsection
