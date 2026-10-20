{{-- Openingstijden uit config/salon.php. 0 = zondag, 1 = maandag, enzovoort. --}}
@php
    $days = [1 => 'Maandag', 2 => 'Dinsdag', 3 => 'Woensdag', 4 => 'Donderdag', 5 => 'Vrijdag', 6 => 'Zaterdag', 0 => 'Zondag'];
    $today = now()->dayOfWeek;
@endphp

<ul {{ $attributes->merge(['class' => 'space-y-1']) }}>
    @foreach ($days as $dow => $label)
        @php($hours = config('salon.opening_hours.'.$dow))
        <li @class(['flex justify-between gap-4', 'font-semibold' => $dow === $today])>
            <span class="inline-flex items-center gap-2">
                @if ($dow === $today)
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                @endif
                {{ $label }}
            </span>
            <span>{{ $hours ? $hours['open'].' – '.$hours['close'] : 'Gesloten' }}</span>
        </li>
    @endforeach
</ul>
