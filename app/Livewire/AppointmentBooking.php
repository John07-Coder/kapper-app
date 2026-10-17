<?php

namespace App\Livewire;

use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use App\Services\AvailabilityService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

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

    /** Na een geslaagde boeking staat hier het id. Dan tonen we het bevestigingsscherm. */
    public ?int $bookedId = null;

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
        $service = new AvailabilityService();
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

        $service = new AvailabilityService();
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

        $service = new AvailabilityService();
        $date = Carbon::parse($this->date);

        // Opnieuw controleren: iemand anders kan dezelfde tijd net geboekt hebben.
        if (! $service->isSlotAvailable($date, $this->time)) {
            $this->time = '';
            $this->loadTimes();
            $this->addError('time', 'Dit tijdstip is helaas net bezet geraakt. Kies een ander tijdstip.');

            return;
        }

        $notes = trim($this->notes);

        $appointment = Appointment::create([
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'phone' => trim($this->phone),
            'date' => $date->toDateString(),
            'start_time' => $this->time,
            'end_time' => $service->endTimeFor($this->time),
            'status' => Appointment::STATUS_PENDING,
            'notes' => $notes === '' ? null : $notes,
        ]);

        Mail::to($appointment->email)->send(new AppointmentConfirmed($appointment));

        $this->bookedId = $appointment->id;
    }

    /**
     * Terug naar een leeg formulier, na de bevestiging.
     */
    public function bookAnother(): void
    {
        $this->bookedId = null;
        $this->time = '';
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->notes = '';
        $this->loadDates();
        $this->loadTimes();
    }

    public function render()
    {
        $booked = null;

        if ($this->bookedId !== null) {
            $booked = Appointment::find($this->bookedId);
        }

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
