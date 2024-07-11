@component('mail::message')
# Email for forget password in NTU Sparks

Hello {{$user->name}}

Dear, {{ $user->name }}
Please use this otp to reset your account : {{ $user->token }}

Thanks from,<br>
{{ config('app.name') }}
@endcomponent
