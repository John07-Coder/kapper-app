<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * E-mail naar de klant nadat een afspraak is aangevraagd.
 * De tekst staat in resources/views/emails/appointment-confirmed.blade.php.
 * De mail gaat via de queue. Mislukt het versturen, dan blijft de afspraak bestaan.
 */
class AppointmentConfirmed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    /**
     * De queue-worker roept dit aan als het versturen definitief mislukt.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Bevestigingsmail kon niet worden verstuurd.', [
            'appointment_id' => $this->appointment->id,
            'message' => $exception->getMessage(),
        ]);
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
