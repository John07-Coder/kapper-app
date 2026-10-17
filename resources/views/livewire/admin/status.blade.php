{{--
    Klein stukje HTML voor de gekleurde status.
    @include plakt dit bestand op de plek waar je het aanroept.
--}}
@if ($appointment->status === \App\Models\Appointment::STATUS_PENDING)
    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">{{ $appointment->statusName() }}</span>
@elseif ($appointment->status === \App\Models\Appointment::STATUS_CONFIRMED)
    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">{{ $appointment->statusName() }}</span>
@else
    <span class="inline-flex rounded-full bg-stone-200 px-2.5 py-0.5 text-xs font-semibold text-stone-700">{{ $appointment->statusName() }}</span>
@endif
