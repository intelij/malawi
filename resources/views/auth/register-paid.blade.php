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
                  data-intent-url="{{ route('payment.intent') }}"
                  autocomplete="off"
                  enctype="multipart/form-data">

                @csrf

                {{-- USER DETAILS --}}
                <div class="form-group">
                    <input type="text" name="first_name" class="form-control input-solid"
                           placeholder="@lang('First Name')" required>
                </div>

                <div class="form-group">
                    <input type="text" name="last_name" class="form-control input-solid"
                           placeholder="@lang('Last Name')" required>
                </div>

                <div class="form-group">
                    <input type="email" name="email" class="form-control input-solid"
                           placeholder="@lang('Email')" required>
                </div>

                <div class="form-group">
                    <input type="text" name="phone" class="form-control input-solid"
                           placeholder="@lang('Phone Number')">
                </div>

                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="birthday" class="form-control input-solid" required>
                </div>

                <div class="form-group">
                    <label>Region</label>
                    <select name="region" class="form-control input-solid" required>
                        <option value="">Select your Region</option>
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
                    <input type="password" name="password"
                           class="form-control input-solid"
                           placeholder="@lang('Password')" required>
                </div>

                <div class="form-group">
                    <input type="password" name="password_confirmation"
                           class="form-control input-solid"
                           placeholder="@lang('Confirm Password')" required>
                </div>

                <hr>

                {{-- PAYMENT --}}
                <div class="form-group">
                    <label>@lang('Registration Fee')</label>
                    <input type="number" step="0.01" name="amount" id="amount"
                           class="form-control input-solid"
                           value="10.00" readonly>
                </div>

                <div class="form-group">
                    <label>Cardholder Name</label>
                    <input type="text" id="cardholder-name"
                           class="form-control input-solid" required>
                </div>

                <div class="form-group">
                    <label>Postcode</label>
                    <input type="text" id="postcode"
                           class="form-control input-solid" required>
                </div>

                <div class="form-group">
                    <label>Card Details</label>
                    <div id="card-element" class="form-control input-solid"></div>
                </div>

                @if (setting('tos'))
                    <div class="form-group mt-3">
                        <input type="checkbox" name="tos" value="1" required>
                        <label>
                            @lang('I accept')
                            <a href="#tos-modal" data-toggle="modal">@lang('Terms of Service')</a>
                        </label>
                    </div>
                @endif

                <div class="form-group mt-4">
                    <button id="submit" class="btn btn-primary w-100">
                        Pay & Register
                    </button>
                </div>

            </form>
        </div>
    </div>

    <div class="text-center text-muted mt-3">
        @lang('Already have an account?')
        <a href="{{ route('login') }}">@lang('Login')</a>
    </div>
</div>

{{-- TOS MODAL --}}
@if (setting('tos'))
<div class="modal fade" id="tos-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>@lang('Terms of Service')</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                @include('auth.tos')
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script src="{{ asset('js/register-payment.js') }}" defer></script>
@endsection
