<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-1">@lang('User Confirmation Flow')</h5>

        <small class="text-muted d-block mb-4">
            @lang('Enable/Disable the user confirmation flow for the application. When enabled, users will be set to Waiting Approval and require admin approval before becoming Active.')
        </small>

        @if (setting('approval.enabled'))
            <form method="POST" action="{{ route('settings.auth.approval.disable') }}" id="auth-approval-settings-form">
                @csrf
                <button type="submit"
                        class="btn btn-danger"
                        data-toggle="loader"
                        data-loading-text="@lang('Disabling...')">
                    @lang('Disable')
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('settings.auth.approval.enable') }}" id="auth-approval-settings-form">
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
