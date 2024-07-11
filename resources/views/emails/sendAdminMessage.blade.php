@component('mail::message')
# Enquiry Email

Hello Assist we have a new Message from admin 

Subject : {{ $email['subject'] }} 
Email : {{ $email['email'] }} 
<br>
Message : {{ $email['message'] }}

Thanks from,<br>
@endcomponent
