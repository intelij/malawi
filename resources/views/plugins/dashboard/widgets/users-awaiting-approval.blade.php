@if ($count)
    <div class="alert alert-warning d-flex justify-content-between align-items-center">
        <p class="mb-0">@lang('There are <strong>:count</strong> user(s) waiting on your approval to be able to access the application.', ['count' => $count])</p>
        <a href="{{ route('users.index', ['status' => \App\Support\Enum\UserStatus::WAITING_APPROVAL]) }}" class="btn btn-primary">
            @lang('Review')
        </a>
    </div>
@endif
