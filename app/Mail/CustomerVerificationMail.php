<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Customer;

class CustomerVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;
    public $plainPassword;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Customer  $customer
     * @param  string  $plainPassword
     * @return void
     */
    public function __construct(Customer $customer, $plainPassword)
    {
        $this->customer = $customer;
        $this->plainPassword = $plainPassword;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
         return $this->subject('Verify Your Email Address')
                     ->markdown('emails.customers.verify');
    }
}
