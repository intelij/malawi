<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function build(): self
    {
        $subject = sprintf('[%s] %s', setting('app_name'), __('Your Account Has Been Approved'));

        return $this->subject($subject)->markdown('mail.user-approved');
    }
}
