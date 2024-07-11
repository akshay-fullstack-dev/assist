@component('mail::message')
# Email for rejection of Agency

Hello {{$user->firstname}}

Your Registration request has been rejected

The reason is:
{{ $user->rejection_reason }}

Thanks from,<br>
{{ config('app.name') }}
@endcomponent