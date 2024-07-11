<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Booking;

class SendAdminMessage extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    /**
     * The booking instance.
     *
     * @var Booking
     */
     
    
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
        return $this->markdown('emails.sendAdminMessage');
    }
}
