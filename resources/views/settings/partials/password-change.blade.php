<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-1">@lang('Force Password Change On Next Login')</h5>

        <small class="text-muted d-block mb-4">
            @lang('Force all the users created manually by admin to reset the password on their first log in to be able to use the application.')
        </small>

        @if (setting('password-change.enabled'))
            <form method="POST" action="{{ route('settings.auth.password-change.disable') }}" id="auth-password-change-settings-form">
                @csrf
                <button type="submit"
                        class="btn btn-danger"
                        data-toggle="loader"
                        data-loading-text="@lang('Disabling...')">
                    @lang('Disable')
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('settings.auth.password-change.enable') }}" id="auth-password-change-settings-form">
                @csrf
                <button type="submit"
                        class="btn btn-primary"
                        data-toggle="loader"
                        data-loading-text="@lang('Enabling...')">
                    @lang('Enable')
                </button>
            </form>
        @endif
    </div>
</div>
