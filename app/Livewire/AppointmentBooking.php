<?php

namespace App\Livewire;

use App\Exceptions\SlotUnavailableException;
use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

/**
 * Het boekingsformulier op /afspraak.
 *
 * Livewire houdt de variabelen hieronder vast tussen klikken door,
 * zonder dat de hele pagina herlaadt.
 * $dates en $times zijn gewone arrays die we zelf vullen in loadDates() en loadTimes().
 */
class AppointmentBooking extends Component
{
    public string $date = '';

    public string $time = '';

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $notes = '';

    /** Lijst met dagen voor de knoppen bovenaan. */
    public array $dates = [];

    /** Vrije tijden van de gekozen dag. */
    public array $times = [];

    /**
     * Bewijs dat déze bezoeker de afspraak net zelf heeft gemaakt.
     * Het afspraak-id staat alleen in de sessie, niet in deze property.
     * #[Locked] zorgt dat de browser het token niet kan vervangen.
     */
    #[Locked]
    public ?string $confirmationToken = null;

    /**
     * mount() draait één keer als de pagina opent.
     */
    public function mount(): void
    {
        $this->loadDates();

        $this->date = today()->toDateString();

        foreach ($this->dates as $day) {
            if ($day['hasSlots']) {
                $this->date = $day['value'];
                break;
            }
        }

        $this->loadTimes();
    }

    /**
     * Vul $dates met de komende open dagen.
     */
    public function loadDates(): void
    {
        $service = new AvailabilityService;
        $this->dates = [];

        foreach ($service->bookableDates() as $day) {
            $freeTimes = $service->availableSlots($day);

            $this->dates[] = [
                'value' => $day->toDateString(),
                'day' => $day->translatedFormat('D'),
                'number' => $day->format('j'),
                'month' => $day->translatedFormat('M'),
                'hasSlots' => count($freeTimes) > 0,
            ];
        }
    }

    /**
     * Vul $times met de vrije tijden van $this->date.
     */
    public function loadTimes(): void
    {
        if ($this->date === '') {
            $this->times = [];

            return;
        }

        $service = new AvailabilityService;
        $this->times = $service->availableSlots(Carbon::parse($this->date));
    }

    public function selectDate(string $date): void
    {
        $this->date = $date;
        $this->time = '';
        $this->loadTimes();
    }

    public function selectTime(string $time): void
    {
        $this->time = $time;
        $this->resetErrorBag('time');
    }

    /**
     * Regels voor het formulier. Laravel geeft een foutmelding als iets niet klopt.
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:8', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Vul je naam in.',
            'name.min' => 'Je naam moet minimaal 2 tekens bevatten.',
            'email.required' => 'Vul je e-mailadres in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'phone.required' => 'Vul je telefoonnummer in.',
            'phone.min' => 'Vul een geldig telefoonnummer in.',
            'notes.max' => 'Opmerkingen mogen maximaal 500 tekens bevatten.',
        ];
    }

    /**
     * Sla de afspraak op en stuur een e-mail.
     * Lokaal komt die e-mail in storage/logs/laravel.log (geen echte mail).
     */
    public function book(): void
    {
        $this->validate();

        if ($this->time === '') {
            $this->addError('time', 'Kies eerst een tijdstip.');

            return;
        }

        $notes = trim($this->notes);
        $date = Carbon::parse($this->date);

        try {
            $appointment = app(BookingService::class)->reserve(
                $date,
                $this->time,
                trim($this->name),
                strtolower(trim($this->email)),
                trim($this->phone),
                $notes === '' ? null : $notes,
            );
        } catch (SlotUnavailableException) {
            $this->time = '';
            $this->loadTimes();
            $this->addError('time', 'Dit tijdstip is helaas net bezet geraakt. Kies een ander tijdstip.');

            return;
        }

        $this->rememberConfirmation($appointment);
        $this->queueConfirmation($appointment);
    }

    /**
     * De afspraak staat al vast. Een mislukte mail mag die boeking niet ongedaan maken.
     */
    private function queueConfirmation(Appointment $appointment): void
    {
        try {
            Mail::to($appointment->email)->queue(
                (new AppointmentConfirmed($appointment))->afterCommit(),
            );
        } catch (Throwable) {
            // AppointmentConfirmed::failed() schrijft de fout weg.
        }
    }

    /**
     * Terug naar een leeg formulier, na de bevestiging.
     */
    public function bookAnother(): void
    {
        session()->forget('booking_confirmation');
        $this->confirmationToken = null;
        $this->time = '';
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->notes = '';
        $this->loadDates();
        $this->loadTimes();
    }

    /**
     * Koppel de bevestiging aan de sessie. Een ander id in de browser levert geen klantgegevens op.
     */
    private function rememberConfirmation(Appointment $appointment): void
    {
        $token = Str::random(40);

        session([
            'booking_confirmation' => [
                'token' => $token,
                'id' => $appointment->id,
            ],
        ]);

        $this->confirmationToken = $token;
    }

    private function confirmedAppointment(): ?Appointment
    {
        $confirmation = session('booking_confirmation');

        if (! is_array($confirmation) || ! is_string($this->confirmationToken)) {
            return null;
        }

        $storedToken = $confirmation['token'] ?? null;

        if (! is_string($storedToken) || ! hash_equals($storedToken, $this->confirmationToken)) {
            return null;
        }

        $id = $confirmation['id'] ?? null;

        if (is_int($id)) {
            $appointmentId = $id;
        } elseif (is_string($id) && ctype_digit($id)) {
            $appointmentId = (int) $id;
        } else {
            return null;
        }

        return Appointment::query()->find($appointmentId);
    }

    public function render()
    {
        $booked = $this->confirmedAppointment();

        $dateLabel = '';

        if ($this->date !== '') {
            $dateLabel = Carbon::parse($this->date)->translatedFormat('l j F Y');
        }

        return view('livewire.appointment-booking', [
            'booked' => $booked,
            'dateLabel' => $dateLabel,
        ]);
    }
}
