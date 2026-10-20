<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Services\AvailabilityService;
use Illuminate\Database\Seeder;

/**
 * Zet een paar voorbeeld-afspraken in de database.
 * Handig om het dashboard meteen te kunnen bekijken.
 * Draait alleen als de tabel nog leeg is.
 */
class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        if (Appointment::count() > 0) {
            return;
        }

        $service = new AvailabilityService;

        $customers = [
            ['Jan de Vries', 'jan@example.com', '06 12345678'],
            ['Sanne Bakker', 'sanne@example.com', '06 23456789'],
            ['Mohammed El Amrani', 'mo@example.com', '06 34567890'],
            ['Lisa Jansen', 'lisa@example.com', '06 45678901'],
            ['Pieter Visser', 'pieter@example.com', '06 56789012'],
        ];

        $statuses = [
            Appointment::STATUS_PENDING,
            Appointment::STATUS_CONFIRMED,
            Appointment::STATUS_CONFIRMED,
        ];

        $dates = $service->bookableDates();
        $dates = array_slice($dates, 0, 5);
        $customerNumber = 0;

        foreach ($dates as $date) {
            $slots = $service->allSlots($date);

            // Drie vaste tijden per dag: de 1e, 5e en 9e tijd in de lijst.
            $chosen = [];

            if (isset($slots[0])) {
                $chosen[] = $slots[0];
            }
            if (isset($slots[4])) {
                $chosen[] = $slots[4];
            }
            if (isset($slots[8])) {
                $chosen[] = $slots[8];
            }

            foreach ($chosen as $index => $slot) {
                $customer = $customers[$customerNumber % count($customers)];
                $customerNumber++;

                $notes = null;

                if ($index === 0) {
                    $notes = 'Graag niet te kort aan de zijkanten.';
                }

                Appointment::create([
                    'name' => $customer[0],
                    'email' => $customer[1],
                    'phone' => $customer[2],
                    'date' => $date->toDateString(),
                    'start_time' => $slot,
                    'end_time' => $service->endTimeFor($slot),
                    'status' => $statuses[$index],
                    'notes' => $notes,
                ]);
            }
        }
    }
}
