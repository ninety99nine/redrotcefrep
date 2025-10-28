<?php

namespace App\Mail;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TeamMemberInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $storeId;
    public $firstName;
    public $verificationUrl;
    public $store;

    /**
     * Create a new message instance.
     *
     * @param string $email
     * @param string|null $firstName
     * @param string $storeId
     * @param string $verificationUrl
     */
    public function __construct(string $email, ?string $firstName, string $storeId, string $verificationUrl)
    {
        $this->email = $email;
        $this->storeId = $storeId;
        $this->firstName = $firstName;
        $this->verificationUrl = $verificationUrl;
        $this->store = Store::findOrFail($storeId);
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You’ve Been Invited to Join {$this->store->name}!",
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
            markdown: 'emails.team-member-invitation',
            with: [
                'email' => $this->email,
                'storeName' => $this->store->name,
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
