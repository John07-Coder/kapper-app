{{-- Eén menulink. $active is true op de pagina waar je nu bent. --}}
@props(['href', 'active' => false])

<li>
    <a href="{{ $href }}"
        @class([
            'transition hover:text-amber-600',
            'text-amber-600' => $active,
            'text-stone-700' => ! $active,
        ])
        @if ($active) aria-current="page" @endif>
        {{ $slot }}
    </a>
</li>
