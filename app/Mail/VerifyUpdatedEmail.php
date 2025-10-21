<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class VerifyUpdatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $firstName;
    public $verificationUrl;

    /**
     * Create a new message instance.
     *
     * @param string $email
     * @param string $firstName
     * @param string $verificationUrl
     */
    public function __construct(string $email, string $firstName, string $verificationUrl)
    {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your New Email Address',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.verify-updated-email',
            with: [
                'email' => $this->email,
                'firstName' => $this->firstName,
                'verificationUrl' => $this->verificationUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
