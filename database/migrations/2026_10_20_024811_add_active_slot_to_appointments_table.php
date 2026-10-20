<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Een actieve afspraak bezet precies één tijdslot.
     * Geannuleerde rijen krijgen NULL, zodat het slot opnieuw geboekt kan worden.
     * Meerdere NULL-waarden zijn toegestaan in een unieke index.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('active_slot', 32)->nullable()->unique();
        });

        $appointments = DB::table('appointments')->select(['id', 'date', 'start_time', 'status'])->get();

        foreach ($appointments as $appointment) {
            $activeSlot = $appointment->status === 'cancelled'
                ? null
                : substr((string) $appointment->date, 0, 10).'|'.substr((string) $appointment->start_time, 0, 5);

            DB::table('appointments')->where('id', $appointment->id)->update([
                'active_slot' => $activeSlot,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique(['active_slot']);
            $table->dropColumn('active_slot');
        });
    }
};
