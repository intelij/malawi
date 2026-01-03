@component('mail::message')

# @lang('Hello!')

@lang('New user was just registered on :app website.', ['app' => setting('app_name')])

@lang('The user is waiting on your approval to be able to access the website.')

@lang('To view the user details just visit the link below.')

@component('mail::button', ['url' => route('users.edit', $user)])
    @lang('View User')
@endcomponent

@lang('Regards'),<br>
{{ setting('app_name') }}

@endcomponent
