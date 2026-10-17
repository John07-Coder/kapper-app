<?php

namespace App\Models;

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
