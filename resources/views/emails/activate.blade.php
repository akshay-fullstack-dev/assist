@component('mail::message')
# Email for activation account in Assist

Hello {{$user->name}}

Dear, {{ $user->name }}
Please use this otp to activate your account : {{ $user->token }}

Thanks from,<br>
{{ config('app.name') }}
@endcomponent
