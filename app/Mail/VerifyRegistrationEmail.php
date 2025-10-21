<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class VerifyRegistrationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $firstName;
    public $verificationUrl;

    public function __construct(string $email, string $firstName, string $verificationUrl)
    {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->verificationUrl = $verificationUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email Address',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.verify-registration-email',
            with: [
                'email' => $this->email,
                'firstName' => $this->firstName,
                'verificationUrl' => $this->verificationUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
