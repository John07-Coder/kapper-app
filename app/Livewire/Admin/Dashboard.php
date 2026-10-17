<?php

namespace App\Livewire\Admin;

use App\Models\Appointment;
use App\Services\AvailabilityService;
use Illuminate\Support\Carbon;
use Livewire\Component;

/**
 * Het dashboard op /admin.
 *
 * Hier ziet de kapper de cijfers, de agenda van een dag
 * en een lijst met afspraken. Knoppen roepen de functies hieronder aan.
 */
class Dashboard extends Component
{
    /** Welke dag de agenda toont, als tekst: 2026-09-08 */
    public string $date = '';

    /** day = één dag, upcoming = vanaf vandaag, all = alles */
    public string $view = 'day';

    public string $status = '';

    public string $search = '';

    public function mount(): void
    {
        $this->date = today()->toDateString();
    }

    public function showDate(string $date): void
    {
        $this->date = $date;
        $this->view = 'day';
    }

    /**
     * Livewire roept deze functie zelf aan als het datumveld verandert.
     * De naam is verplicht: updated + de naam van de variabele (Date).
     */
    public function updatedDate(): void
    {
        if ($this->date === '') {
            $this->date = today()->toDateString();
        }

        $this->view = 'day';
    }

    public function previousDay(): void
    {
        $this->date = Carbon::parse($this->date)->subDay()->toDateString();
        $this->view = 'day';
    }

    public function nextDay(): void
    {
        $this->date = Carbon::parse($this->date)->addDay()->toDateString();
        $this->view = 'day';
    }

    public function setView(string $view): void
    {
        if ($view === 'day' || $view === 'upcoming' || $view === 'all') {
            $this->view = $view;
        }
    }

    public function confirm(int $id): void
    {
        $appointment = Appointment::find($id);

        if ($appointment !== null) {
            $appointment->status = Appointment::STATUS_CONFIRMED;
            $appointment->save();
        }
    }

    public function cancel(int $id): void
    {
        $appointment = Appointment::find($id);

        if ($appointment !== null) {
            $appointment->status = Appointment::STATUS_CANCELLED;
            $appointment->save();
        }
    }

    /**
     * Zet een geannuleerde afspraak terug.
     * Dat mag niet als er intussen iemand anders op die tijd staat.
     */
    public function restore(int $id): void
    {
        $appointment = Appointment::find($id);

        if ($appointment === null) {
            return;
        }

        $others = Appointment::query()
            ->whereDate('date', $appointment->date->toDateString())
            ->where('id', '!=', $appointment->id)
            ->get();

        foreach ($others as $other) {
            $sameTime = $other->startLabel() === $appointment->startLabel();
            $stillActive = ! $other->isCancelled();

            if ($sameTime && $stillActive) {
                $this->addError('restore', 'Dit tijdslot is inmiddels door een andere afspraak bezet.');

                return;
            }
        }

        $appointment->status = Appointment::STATUS_PENDING;
        $appointment->save();
    }

    public function delete(int $id): void
    {
        $appointment = Appointment::find($id);

        if ($appointment !== null) {
            $appointment->delete();
        }
    }

    public function render()
    {
        $service = new AvailabilityService();
        $selectedDay = Carbon::parse($this->date);

        $stats = $this->stats();
        $week = $this->weekDays($service);
        $agenda = $this->agendaFor($selectedDay, $service);
        $pending = $this->pendingAppointments();
        $appointments = $this->filteredAppointments();

        $hour = (int) now()->format('H');

        if ($hour < 12) {
            $greeting = 'Goedemorgen';
        } elseif ($hour < 18) {
            $greeting = 'Goedemiddag';
        } else {
            $greeting = 'Goedenavond';
        }

        return view('livewire.admin.dashboard', [
            'greeting' => $greeting,
            'userName' => auth()->user()->name,
            'todayLabel' => today()->translatedFormat('l j F Y'),
            'dateLabel' => $selectedDay->translatedFormat('l j F Y'),
            'isOpen' => $service->isOpen($selectedDay),
            'stats' => $stats,
            'week' => $week,
            'agenda' => $agenda,
            'pending' => $pending,
            'appointments' => $appointments,
        ]);
    }

    /**
     * De vier cijfers bovenaan het dashboard.
     */
    private function stats(): array
    {
        $todayCount = 0;
        $pendingCount = 0;
        $upcomingCount = 0;

        $all = Appointment::query()->orderBy('date')->get();

        foreach ($all as $appointment) {
            $isToday = $appointment->date->toDateString() === today()->toDateString();
            $isUpcoming = $appointment->date->toDateString() >= today()->toDateString();

            if ($isToday && ! $appointment->isCancelled()) {
                $todayCount++;
            }

            if ($appointment->status === Appointment::STATUS_PENDING && $isUpcoming) {
                $pendingCount++;
            }

            if ($isUpcoming && ! $appointment->isCancelled()) {
                $upcomingCount++;
            }
        }

        $price = (float) config('salon.service.price');

        return [
            'today' => $todayCount,
            'pending' => $pendingCount,
            'upcoming' => $upcomingCount,
            'revenue' => $todayCount * $price,
        ];
    }

    /**
     * De eerstvolgende 6 open dagen, met het aantal afspraken.
     */
    private function weekDays(AvailabilityService $service): array
    {
        $days = [];
        $cursor = today()->copy();
        $safety = 0;

        while (count($days) < 6 && $safety < 21) {
            if ($service->isOpen($cursor)) {
                $count = 0;
                $onThisDay = Appointment::query()->whereDate('date', $cursor->toDateString())->get();

                foreach ($onThisDay as $appointment) {
                    if (! $appointment->isCancelled()) {
                        $count++;
                    }
                }

                $days[] = [
                    'date' => $cursor->toDateString(),
                    'name' => $cursor->translatedFormat('D'),
                    'number' => $cursor->format('j'),
                    'month' => $cursor->translatedFormat('M'),
                    'count' => $count,
                ];
            }

            $cursor->addDay();
            $safety++;
        }

        return $days;
    }

    /**
     * Eén rij per tijdslot. "appointment" is null als de tijd vrij is.
     */
    private function agendaFor(Carbon $day, AvailabilityService $service): array
    {
        $appointments = Appointment::query()
            ->whereDate('date', $day->toDateString())
            ->orderBy('start_time')
            ->get();

        $rows = [];

        foreach ($service->allSlots($day) as $time) {
            $found = null;

            foreach ($appointments as $appointment) {
                if ($appointment->startLabel() === $time && ! $appointment->isCancelled()) {
                    $found = $appointment;
                    break;
                }
            }

            $rows[] = [
                'time' => $time,
                'appointment' => $found,
            ];
        }

        return $rows;
    }

    /**
     * Afspraken die de kapper nog moet bevestigen.
     */
    private function pendingAppointments()
    {
        return Appointment::query()
            ->where('status', Appointment::STATUS_PENDING)
            ->whereDate('date', '>=', today()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * De tabel onderaan, met de gekozen filters.
     */
    private function filteredAppointments()
    {
        $query = Appointment::query();

        if ($this->view === 'day') {
            $query->whereDate('date', $this->date);
        } elseif ($this->view === 'upcoming') {
            $query->whereDate('date', '>=', today()->toDateString());
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        $search = trim($this->search);

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        return $query->orderBy('date')->orderBy('start_time')->get();
    }
}
