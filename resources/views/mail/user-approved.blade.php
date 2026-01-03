@component('mail::message')

# @lang('Hello!')

@lang('Your account has been approved by administrators. You can now log in now and start using the application.')

<br>

@lang('Regards'),<br>
{{ setting('app_name') }}

@endcomponent
