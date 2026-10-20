<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Een afspraak in de database (tabel: appointments).
 *
 * Elke property hieronder ($name, $date, ...) hoort bij een kolom.
 * Die vult Laravel automatisch als je een afspraak ophaalt.
 */
class Appointment extends Model
{
    // Deze statussen staan als Engelse woorden in de database.
    // Op het scherm gebruiken we de Nederlandse naam (zie statusName()).
    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Alleen deze kolommen mogen via Appointment::create() worden ingevuld.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'date',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    /**
     * Zet de kolom "date" om naar een datum-object.
     * Daarna kun je bijvoorbeeld $appointment->date->format('d-m-Y') doen.
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Appointment $appointment): void {
            $appointment->active_slot = $appointment->isCancelled()
                ? null
                : self::slotKey($appointment->dateKey(), (string) $appointment->start_time);
        });
    }

    /**
     * Sleutel van een bezet tijdslot, bijvoorbeeld 2026-09-08|10:00.
     * Die waarde staat uniek in de database zolang de afspraak niet geannuleerd is.
     */
    public static function slotKey(string $date, string $time): string
    {
        return substr($date, 0, 10).'|'.substr($time, 0, 5);
    }

    public function dateKey(): string
    {
        if ($this->date instanceof CarbonInterface) {
            return $this->date->toDateString();
        }

        return substr((string) $this->date, 0, 10);
    }

    /**
     * Tijd op het scherm, bijvoorbeeld 09:30.
     * In MySQL staat een tijd soms als 09:30:00. We houden alleen de eerste 5 tekens.
     */
    public function startLabel(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function endLabel(): string
    {
        return substr((string) $this->end_time, 0, 5);
    }

    /**
     * Nederlandse naam van de status.
     */
    public function statusName(): string
    {
        if ($this->status === self::STATUS_PENDING) {
            return 'In afwachting';
        }

        if ($this->status === self::STATUS_CONFIRMED) {
            return 'Bevestigd';
        }

        if ($this->status === self::STATUS_CANCELLED) {
            return 'Geannuleerd';
        }

        return $this->status;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
