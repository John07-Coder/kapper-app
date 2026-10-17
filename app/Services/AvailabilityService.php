<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Carbon;

/**
 * Berekent welke dagen en tijden nog vrij zijn.
 *
 * De openingstijden en de lengte van een afspraak staan in config/salon.php.
 * Deze class verandert niets in de database. Hij geeft alleen lijsten terug.
 */
class AvailabilityService
{
    /**
     * Openingstijden van één dag.
     * Geeft null terug als de salon die dag dicht is.
     * In de config is 0 = zondag, 1 = maandag, ... 6 = zaterdag.
     */
    public function openingHoursFor(Carbon $date): ?array
    {
        return config('salon.opening_hours.'.$date->dayOfWeek);
    }

    public function isOpen(Carbon $date): bool
    {
        return $this->openingHoursFor($date) !== null;
    }

    /**
     * Hoeveel minuten één afspraak duurt. Staat in config/salon.php.
     */
    public function slotMinutes(): int
    {
        return (int) config('salon.service.duration');
    }

    /**
     * Alle tijden op een dag, ook als ze al bezet zijn.
     * Voorbeeld bij 09:00-18:00 en 30 minuten: ["09:00", "09:30", ... "17:30"].
     */
    public function allSlots(Carbon $date): array
    {
        $hours = $this->openingHoursFor($date);

        if ($hours === null) {
            return [];
        }

        $slots = [];
        $time = $hours['open'];
        $close = $hours['close'];
        $minutes = $this->slotMinutes();

        // Tijden zijn altijd "uu:mm" (09:00). Daardoor kun je ze als tekst vergelijken.
        while ($this->addMinutes($time, $minutes) <= $close) {
            $slots[] = $time;
            $time = $this->addMinutes($time, $minutes);
        }

        return $slots;
    }

    /**
     * Tijden die al bezet zijn. Geannuleerde afspraken tellen niet mee.
     */
    public function bookedSlots(Carbon $date): array
    {
        $appointments = Appointment::query()
            ->where('status', '!=', Appointment::STATUS_CANCELLED)
            ->whereDate('date', $date->toDateString())
            ->get();

        $booked = [];

        foreach ($appointments as $appointment) {
            $booked[] = $appointment->startLabel();
        }

        return $booked;
    }

    /**
     * Tijden die de klant nog mag kiezen.
     * Verstreken tijden van vandaag zitten er niet in.
     */
    public function availableSlots(Carbon $date): array
    {
        if (! $this->isBookableDate($date)) {
            return [];
        }

        $booked = $this->bookedSlots($date);
        $now = now()->format('H:i');
        $free = [];

        foreach ($this->allSlots($date) as $slot) {
            if (in_array($slot, $booked, true)) {
                continue;
            }

            if ($date->isToday() && $slot <= $now) {
                continue;
            }

            $free[] = $slot;
        }

        return $free;
    }

    /**
     * Mag een klant op deze datum boeken?
     * De salon moet open zijn, de dag mag niet voorbij zijn,
     * en de dag moet binnen het aantal dagen uit de config vallen.
     */
    public function isBookableDate(Carbon $date): bool
    {
        if (! $this->isOpen($date)) {
            return false;
        }

        if ($date->toDateString() < today()->toDateString()) {
            return false;
        }

        $lastDay = today()->addDays((int) config('salon.booking_window_days'))->toDateString();

        return $date->toDateString() <= $lastDay;
    }

    /**
     * Alle dagen waarop geboekt mag worden, vanaf vandaag.
     * Elke waarde is een Carbon-datum (een datum-object van Laravel).
     */
    public function bookableDates(): array
    {
        $dates = [];
        $day = today()->copy();
        $lastDay = today()->addDays((int) config('salon.booking_window_days'))->toDateString();

        while ($day->toDateString() <= $lastDay) {
            if ($this->isOpen($day)) {
                $dates[] = $day->copy();
            }

            $day->addDay();
        }

        return $dates;
    }

    public function isSlotAvailable(Carbon $date, string $time): bool
    {
        return in_array($time, $this->availableSlots($date), true);
    }

    /**
     * Eindtijd bij een starttijd. 09:00 met 30 minuten wordt 09:30.
     */
    public function endTimeFor(string $startTime): string
    {
        return $this->addMinutes($startTime, $this->slotMinutes());
    }

    /**
     * Telt minuten op bij een tijd als tekst.
     * "09:00" + 30 wordt "09:30".
     */
    public function addMinutes(string $time, int $minutes): string
    {
        $parts = explode(':', $time);
        $totalMinutes = ((int) $parts[0] * 60) + (int) $parts[1] + $minutes;

        $hours = intdiv($totalMinutes, 60);
        $mins = $totalMinutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }
}
