@component('mail::message')
# Enquiry Email

Hello {{ $email['firstname'] }} {{ $email['lastname'] }} we have a new enquiry from user

Subject : {{ $email['subject'] }} 
Email : {{ $email['email'] }} 
<br>
Message : {{ $email['message'] }}

Thanks from,<br>
@endcomponent
