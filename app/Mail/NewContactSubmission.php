<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class NewContactSubmission extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contact $contact)
    {
    }

    public function build(): self
    {
        $topic = $this->contact->topic ? Str::headline($this->contact->topic) : 'Website';
        $subject = 'New Website Contact: '.$topic;

        return $this
            ->subject(str_replace(["\r", "\n"], ' ', $subject))
            ->replyTo($this->contact->email, str_replace(["\r", "\n"], ' ', $this->contact->name))
            ->view('emails.contact.new-submission')
            ->text('emails.contact.new-submission-text');
    }
}
