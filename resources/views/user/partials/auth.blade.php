@php
    $showForcePasswordChange = $edit && !$profile && $user?->id !== auth()?->user()?->id && setting('password-change.enabled');
@endphp

<div class="form-group">
    <label for="email">@lang('Email')</label>
    <input type="email"
           class="form-control input-solid"
           id="email"
           name="email"
           placeholder="@lang('Email')"
           value="{{ $edit ? $user->email : '' }}">
</div>

<div class="form-group">
    <label for="username">@lang('Username')</label>
    <input type="text"
           class="form-control input-solid"
           id="username"
           placeholder="(@lang('optional'))"
           name="username"
           value="{{ $edit ? $user->username : '' }}">
</div>

<div class="form-group">
    <label for="password">{{ $edit ? __("New Password") : __('Password') }}</label>
    <input type="password"
           class="form-control input-solid"
           id="password"
           name="password"
           @if ($edit) placeholder="@lang("Leave field blank if you don't want to change it")" @endif>
</div>

<div class="form-group">
    <label for="password_confirmation">{{ $edit ? __("Confirm New Password") : __('Confirm Password') }}</label>
    <input type="password"
           class="form-control input-solid"
           id="password_confirmation"
           name="password_confirmation"
           @if ($edit) placeholder="@lang("Leave field blank if you don't want to change it")" @endif>
</div>

@if($showForcePasswordChange)
    <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input"
               name="force_password_change"
               id="force_password_change"
            @checked(($edit ? $user->force_password_change : setting('password-change.enabled'))) />
        <label class="custom-control-label font-weight-normal" for="force_password_change">
            @lang('Force Password Change on next login')
        </label>
    </div>
@endif

@if ($edit)
    <button type="submit"
            class="btn btn-primary {{ $showForcePasswordChange ? 'mt-4' : 'mt-2' }}"
            id="update-login-details-btn">
        <i class="fa fa-refresh"></i>
        @lang('Update Details')
    </button>
@endif
