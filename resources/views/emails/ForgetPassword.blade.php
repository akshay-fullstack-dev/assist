@component('mail::message')
# Email for forget password in NTU Sparks

Hello {{$user->name}}

Dear, {{ $user->name }}
Please use this otp to reset your account : {{ $user->password_otp }}

Thanks from,<br>
{{ config('app.name') }}
@endcomponent
