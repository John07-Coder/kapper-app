<x-mail::message>
# Bedankt voor je afspraak, {{ $appointment->name }}!

We hebben je afspraak ontvangen. Hieronder vind je de gegevens:

<x-mail::table>
| | |
|:--|:--|
| **Behandeling** | {{ config('salon.service.name') }} |
| **Datum** | {{ $appointment->date->translatedFormat('l j F Y') }} |
| **Tijd** | {{ $appointment->startLabel() }} – {{ $appointment->endLabel() }} |
| **Prijs** | € {{ number_format(config('salon.service.price'), 2, ',', '.') }} (betalen in de salon) |
</x-mail::table>

@if ($appointment->notes)
**Jouw opmerking:** {{ $appointment->notes }}
@endif

Kun je toch niet komen? Laat het ons dan minimaal 24 uur van tevoren weten via {{ config('salon.phone') }}.

<x-mail::button :url="route('appointment')">
Nog een afspraak maken
</x-mail::button>

Tot snel!<br>
{{ config('salon.name') }}<br>
{{ config('salon.address') }}
</x-mail::message>
