<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gegevens van de kapsalon
    |--------------------------------------------------------------------------
    */

    'name' => env('APP_NAME', 'Kapsalon De Knip'),
    'address' => 'Hoofdstraat 12, 1234 AB Voorbeeldstad',
    'phone' => '012 - 345 67 89',
    'email' => 'info@kapsalondeknip.nl',

    /*
    |--------------------------------------------------------------------------
    | Dienst
    |--------------------------------------------------------------------------
    | Er is één dienst: knippen. Betaling gebeurt in de salon.
    */

    'service' => [
        'name' => 'Knippen',
        'price' => 25.00,
        'duration' => 30, // minuten, tevens de lengte van een tijdslot
    ],

    /*
    |--------------------------------------------------------------------------
    | Openingstijden
    |--------------------------------------------------------------------------
    | Sleutel = dag van de week (0 = zondag ... 6 = zaterdag).
    | null = gesloten.
    */

    'opening_hours' => [
        0 => null,                          // zondag
        1 => null,                          // maandag
        2 => ['open' => '09:00', 'close' => '18:00'], // dinsdag
        3 => ['open' => '09:00', 'close' => '18:00'], // woensdag
        4 => ['open' => '09:00', 'close' => '18:00'], // donderdag
        5 => ['open' => '09:00', 'close' => '18:00'], // vrijdag
        6 => ['open' => '09:00', 'close' => '18:00'], // zaterdag
    ],

    /*
    |--------------------------------------------------------------------------
    | Boekingsvenster
    |--------------------------------------------------------------------------
    | Hoeveel dagen vooruit klanten mogen boeken.
    */

    'booking_window_days' => 30,

];
