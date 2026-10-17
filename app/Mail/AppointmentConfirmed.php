<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * E-mail naar de klant nadat een afspraak is aangevraagd.
 * De tekst staat in resources/views/emails/appointment-confirmed.blade.php.
 * In .env staat MAIL_MAILER=log, dus de mail wordt alleen in het logbestand gezet.
 */
class AppointmentConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bevestiging van je afspraak bij '.config('salon.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointment-confirmed',
        );
    }
}
