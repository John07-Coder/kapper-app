<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * E-mail naar de salon als iemand het contactformulier invult.
 * replyTo zorgt dat "beantwoorden" naar de klant gaat, niet naar de salon zelf.
 */
class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nieuw bericht via de website van '.$this->senderName,
            replyTo: [new Address($this->senderEmail, $this->senderName)],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-message',
        );
    }
}
