<?php

namespace App\Services;

use App\Exceptions\SlotUnavailableException;
use App\Models\Appointment;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Reserveert een tijdslot in één transactie.
 * De unieke kolom active_slot vangt twee gelijktijdige aanvragen op.
 */
class BookingService
{
    public function __construct(private AvailabilityService $availability) {}

    /**
     * @throws SlotUnavailableException
     */
    public function reserve(
        Carbon $date,
        string $time,
        string $name,
        string $email,
        string $phone,
        ?string $notes,
    ): Appointment {
        try {
            return DB::transaction(function () use ($date, $time, $name, $email, $phone, $notes): Appointment {
                $slot = Appointment::slotKey($date->toDateString(), $time);

                Appointment::query()
                    ->where('active_slot', $slot)
                    ->lockForUpdate()
                    ->get();

                if (! $this->availability->isSlotAvailable($date, $time)) {
                    throw new SlotUnavailableException;
                }

                return Appointment::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'date' => $date->toDateString(),
                    'start_time' => $time,
                    'end_time' => $this->availability->endTimeFor($time),
                    'status' => Appointment::STATUS_PENDING,
                    'notes' => $notes,
                ]);
            });
        } catch (UniqueConstraintViolationException $exception) {
            throw new SlotUnavailableException(previous: $exception);
        }
    }
}
