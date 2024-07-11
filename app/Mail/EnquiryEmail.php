<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EnquiryEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        
        $this->email = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
         
        return $this->markdown('emails.enquiryEmail');
    }
}
