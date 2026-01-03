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


                                @if (setting('tos'))

                                    <div class="form-group">
                                        <input type="checkbox" class="custom-control-input" name="tos" id="tos" value="1" required/>
                                        <label class="custom-control-label font-weight-normal" for="tos">
                                            @lang('I accept')
                                            <a href="#tos-modal" data-toggle="modal">@lang('Terms of Service')</a>
                                        </label>
                                    </div>

                                    {{-- <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="tos" id="tos" value="1"/>
                                        <label class="custom-control-label font-weight-normal" for="tos">
                                            @lang('I accept')
                                            <a href="#tos-modal" data-toggle="modal">@lang('Terms of Service')</a>
                                        </label>
                                    </div> --}}
                                @endif

                                {{-- Only display captcha if it is enabled --}}
                                @if (setting('registration.captcha.enabled'))
                                    <div class="form-group my-4">
                                        {!! app('captcha')->display() !!}
                                    </div>
                                @endif
                                {{-- end captcha --}}

                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block careox-btn" id="btn-login" > {{-- onclick="this.innerHTML = 'Processing ...!'" --}}
                                        @lang('Register')
                                    </button>
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
