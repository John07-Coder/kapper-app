<x-layouts.app title="Over ons">
    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">Over ons</p>
            <h1 class="mt-2 text-4xl font-bold text-stone-900">Een kapper zoals een kapper hoort te zijn</h1>
            <p class="mt-4 max-w-2xl text-lg text-stone-600">
                {{ config('salon.name') }} is een kleine, zelfstandige kapsalon in het hart van de buurt. Geen fratsen, geen
                ingewikkelde prijslijst — gewoon een goede knipbeurt door iemand die z'n vak verstaat.
            </p>
        </div>
    </section>

    <section class="mx-auto grid max-w-6xl gap-12 px-4 py-16 sm:px-6 md:grid-cols-2">
        <div class="flex aspect-[4/3] items-center justify-center rounded-2xl bg-gradient-to-br from-stone-800 to-stone-600 text-stone-300 shadow-lg">
            <div class="text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.848 8.25l1.536.887M7.848 8.25a3 3 0 11-5.196-3 3 3 0 015.196 3zm1.536.887a2.165 2.165 0 011.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l2.077 1.199M7.848 15.75l1.536-.887m-1.536.887a3 3 0 11-5.196 3 3 3 0 015.196-3zm1.536-.887a2.165 2.165 0 001.083-1.838c.005-.352.054-.695.14-1.025m-1.223 2.863l2.077-1.199m0-3.328a4.323 4.323 0 012.068-1.379l5.325-1.628a4.5 4.5 0 012.48-.044l.803.215-7.794 4.5m-2.882-1.664A4.331 4.331 0 0010.607 12m3.736 0l7.794 4.5-.802.215a4.5 4.5 0 01-2.48-.043l-5.326-1.629a4.324 4.324 0 01-2.068-1.379M14.343 12l-2.882 1.664" />
                </svg>
                <p class="mt-3 text-sm">Foto van de salon</p>
            </div>
        </div>

        <div class="space-y-6 text-stone-700">
            <h2 class="text-2xl font-bold text-stone-900">Ons verhaal</h2>
            <p>
                In 2009 openden we de deuren met één stoel, één schaar en een flinke dosis enthousiasme. Inmiddels zijn we
                een vast adres voor buurtbewoners die houden van een eerlijk gesprek en een kapsel dat écht bij ze past.
            </p>
            <p>
                We werken uitsluitend op afspraak. Zo hoef jij nooit te wachten en kunnen wij alle aandacht aan jou geven.
                Een knipbeurt duurt ongeveer {{ config('salon.service.duration') }} minuten en kost
                € {{ number_format(config('salon.service.price'), 2, ',', '.') }}. Je betaalt gewoon in de salon, contant of met pin.
            </p>

            <h2 class="pt-4 text-2xl font-bold text-stone-900">Waar we voor staan</h2>
            <ul class="space-y-3">
                <li class="flex gap-3">
                    <span class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs text-white">✓</span>
                    <span><strong>Persoonlijk advies</strong> – we luisteren eerst, knippen daarna.</span>
                </li>
                <li class="flex gap-3">
                    <span class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs text-white">✓</span>
                    <span><strong>Eerlijke prijs</strong> – één tarief, geen verrassingen.</span>
                </li>
                <li class="flex gap-3">
                    <span class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs text-white">✓</span>
                    <span><strong>Altijd op tijd</strong> – jouw afspraak is jouw moment.</span>
                </li>
            </ul>

            <a href="{{ route('appointment') }}" class="inline-flex items-center rounded-lg bg-amber-500 px-5 py-3 font-semibold text-white shadow hover:bg-amber-400">
                Afspraak maken
            </a>
        </div>
    </section>
</x-layouts.app>
