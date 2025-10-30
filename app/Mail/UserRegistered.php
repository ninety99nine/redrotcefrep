<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class UserRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $firstName;

    public function __construct(string $email, string $firstName)
    {
        $this->email = $email;
        $this->firstName = $firstName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to '.config('app.name').'!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.user-registered',
            with: [
                'email' => $this->email,
                'firstName' => $this->firstName
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
