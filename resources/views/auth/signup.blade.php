
                    <form role="form" action="<?= url('register') ?>" method="post" id="registration-form" autocomplete="off" class="mt-3">
                        <input type="hidden" value="<?= csrf_token() ?>" name="_token">
                        <div class="form-group">
                            <input type="email"
                                   name="email"
                                   id="email"
                                   class="form-control input-solid"
                                   placeholder="@lang('Email')"
                                   value="{{ old('email') }}">
                        </div>
                        <div class="form-group">
                            <input type="text"
                                   name="username"
                                   id="username"
                                   class="form-control input-solid"
                                   placeholder="@lang('Username')"
                                   value="{{ old('username') }}">
                        </div>
                        <div class="form-group">
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control input-solid"
                                   placeholder="@lang('Password')">
                        </div>
                         <div class="form-group">
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   class="form-control input-solid"
                                   placeholder="@lang('Confirm Password')">
                        </div>

                        @if (setting('tos'))
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="tos" id="tos" value="1"/>
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

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-lg btn-block" id="btn-login">
                                @lang('Register')
                            </button>
                        </div>
                    </form>
