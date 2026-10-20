<?php

namespace Tests\Feature;

use App\Livewire\AppointmentBooking;
use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use App\Services\AvailabilityService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $tuesday;

    protected function setUp(): void
    {
        parent::setUp();

        // Vaste "nu": maandag 7 september 2026, 10:00 (salon gesloten op maandag).
        Carbon::setTestNow(Carbon::parse('2026-09-07 10:00:00'));
        $this->tuesday = Carbon::parse('2026-09-08');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_slots_follow_opening_hours(): void
    {
        $availability = app(AvailabilityService::class);

        $slots = $availability->allSlots($this->tuesday);

        $this->assertCount(18, $slots);
        $this->assertSame('09:00', $slots[0]);
        $this->assertSame('17:30', $slots[count($slots) - 1]);
        $this->assertCount(0, $availability->allSlots(Carbon::parse('2026-09-07'))); // maandag
        $this->assertCount(0, $availability->allSlots(Carbon::parse('2026-09-13'))); // zondag
    }

    public function test_booked_and_past_slots_are_excluded(): void
    {
        $availability = app(AvailabilityService::class);

        Appointment::create($this->payload(['start_time' => '10:00', 'end_time' => '10:30']));
        Appointment::create($this->payload(['start_time' => '11:00', 'end_time' => '11:30', 'status' => Appointment::STATUS_CANCELLED]));

        $slots = $availability->availableSlots($this->tuesday);

        $this->assertNotContains('10:00', $slots);
        $this->assertContains('11:00', $slots);

        // Vandaag (dinsdag 12:10): tijden vóór nu vallen weg.
        Carbon::setTestNow(Carbon::parse('2026-09-08 12:10:00'));
        $today = $availability->availableSlots(Carbon::parse('2026-09-08'));
        $this->assertNotContains('12:00', $today);
        $this->assertContains('12:30', $today);
    }

    public function test_customer_can_book_an_appointment(): void
    {
        Mail::fake();

        Livewire::test(AppointmentBooking::class)
            ->assertSet('date', '2026-09-08')
            ->call('selectDate', '2026-09-09')
            ->call('selectTime', '14:00')
            ->set('name', 'Jan Jansen')
            ->set('email', 'JAN@example.com')
            ->set('phone', '0612345678')
            ->set('notes', 'Kort aan de zijkanten')
            ->call('book')
            ->assertHasNoErrors()
            ->assertSee('Je afspraak is aangevraagd!')
            ->assertSee('woensdag 9 september 2026')
            ->assertSee('14:00 – 14:30');

        $appointment = Appointment::where('email', 'jan@example.com')->firstOrFail();
        $this->assertSame('Jan Jansen', $appointment->name);
        $this->assertSame('2026-09-09', $appointment->date->toDateString());
        $this->assertSame('14:00', $appointment->startLabel());
        $this->assertSame('14:30', $appointment->endLabel());
        $this->assertSame(Appointment::STATUS_PENDING, $appointment->status);
        $this->assertSame('Kort aan de zijkanten', $appointment->notes);

        Mail::assertQueued(AppointmentConfirmed::class, function ($mail) {
            return $mail->hasTo('jan@example.com');
        });
    }

    public function test_booking_requires_time_and_valid_details(): void
    {
        Livewire::test(AppointmentBooking::class)
            ->set('name', 'J')
            ->set('email', 'geen-email')
            ->set('phone', '123')
            ->call('book')
            ->assertHasErrors(['name', 'email', 'phone']);

        Livewire::test(AppointmentBooking::class)
            ->set('name', 'Jan Jansen')
            ->set('email', 'jan@example.com')
            ->set('phone', '0612345678')
            ->call('book')
            ->assertHasErrors(['time']);

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_double_booking_is_prevented(): void
    {
        Mail::fake();

        Appointment::create($this->payload(['start_time' => '10:00', 'end_time' => '10:30']));

        Livewire::test(AppointmentBooking::class)
            ->call('selectDate', '2026-09-08')
            ->call('selectTime', '10:00') // via UI niet aanklikbaar, maar we forceren het
            ->set('name', 'Piet Pieters')
            ->set('email', 'piet@example.com')
            ->set('phone', '0687654321')
            ->call('book')
            ->assertHasErrors(['time']);

        $this->assertDatabaseCount('appointments', 1);
        Mail::assertNothingOutgoing();
    }

    public function test_confirmation_does_not_reveal_another_customers_details(): void
    {
        Mail::fake();

        Appointment::create($this->payload([
            'name' => 'Geheime Klant',
            'email' => 'geheim@example.com',
            'start_time' => '11:00',
            'end_time' => '11:30',
        ]));

        session([
            'booking_confirmation' => [
                'token' => 'gestolen-token',
                'id' => Appointment::query()->where('email', 'geheim@example.com')->value('id'),
            ],
        ]);

        Livewire::test(AppointmentBooking::class)
            ->assertDontSee('geheim@example.com')
            ->assertDontSee('Geheime Klant')
            ->assertDontSee('Je afspraak is aangevraagd!');

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(AppointmentBooking::class)->set('confirmationToken', 'gestolen-token');
    }

    public function test_database_rejects_a_second_active_booking_for_the_same_slot(): void
    {
        Appointment::create($this->payload([
            'start_time' => '10:00',
            'end_time' => '10:30',
        ]));

        $this->expectException(UniqueConstraintViolationException::class);

        Appointment::create($this->payload([
            'email' => 'tweede@example.com',
            'start_time' => '10:00',
            'end_time' => '10:30',
        ]));
    }

    public function test_cancelled_slot_can_be_booked_again(): void
    {
        Mail::fake();

        $cancelled = Appointment::create($this->payload([
            'start_time' => '15:00',
            'end_time' => '15:30',
            'status' => Appointment::STATUS_CANCELLED,
        ]));

        $this->assertNull($cancelled->fresh()->active_slot);

        Livewire::test(AppointmentBooking::class)
            ->call('selectDate', '2026-09-08')
            ->call('selectTime', '15:00')
            ->set('name', 'Nieuwe Klant')
            ->set('email', 'nieuw@example.com')
            ->set('phone', '0612345678')
            ->call('book')
            ->assertHasNoErrors()
            ->assertSee('Je afspraak is aangevraagd!')
            ->assertSee('nieuw@example.com')
            ->assertDontSee('bestaand@example.com');

        $this->assertSame(Appointment::STATUS_CANCELLED, $cancelled->fresh()->status);
        $this->assertSame('2026-09-08|15:00', Appointment::query()->where('email', 'nieuw@example.com')->value('active_slot'));
    }

    public function test_booking_succeeds_when_the_confirmation_mail_fails(): void
    {
        $logged = [];

        Log::listen(function (MessageLogged $event) use (&$logged): void {
            $logged[] = $event->message;
        });

        Mail::extend('failing', function () {
            return new class implements TransportInterface
            {
                public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
                {
                    throw new TransportException('smtp down');
                }

                public function __toString(): string
                {
                    return 'failing';
                }
            };
        });

        config([
            'mail.default' => 'failing',
            'mail.mailers.failing' => ['transport' => 'failing'],
        ]);

        Livewire::test(AppointmentBooking::class)
            ->call('selectDate', '2026-09-08')
            ->call('selectTime', '16:00')
            ->set('name', 'Jan Jansen')
            ->set('email', 'jan@example.com')
            ->set('phone', '0612345678')
            ->call('book')
            ->assertHasNoErrors()
            ->assertSee('Je afspraak is aangevraagd!')
            ->assertSee('jan@example.com');

        $this->assertNotNull(Appointment::query()->where('email', 'jan@example.com')->first());
        $this->assertContains('Bevestigingsmail kon niet worden verstuurd.', $logged);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Bestaande Klant',
            'email' => 'bestaand@example.com',
            'phone' => '0600000000',
            'date' => $this->tuesday->toDateString(),
            'start_time' => '09:00',
            'end_time' => '09:30',
            'status' => Appointment::STATUS_CONFIRMED,
        ], $overrides);
    }
}
