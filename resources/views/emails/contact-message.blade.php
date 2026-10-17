<x-mail::message>
# Nieuw bericht via het contactformulier

**Van:** {{ $senderName }} ({{ $senderEmail }})

<x-mail::panel>
{{ $body }}
</x-mail::panel>

Je kunt direct op deze e-mail antwoorden om te reageren.

{{ config('salon.name') }}
</x-mail::message>
