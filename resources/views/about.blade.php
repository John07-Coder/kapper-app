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
        <div class="flex aspect-[4/3] flex-col justify-between rounded-3xl bg-stone-900 p-8 text-white shadow-xl">
            <p class="text-sm font-semibold uppercase tracking-widest text-amber-400">Sinds 2009</p>
            <div>
                <p class="text-4xl font-bold leading-tight">Eén stoel.<br>Alle aandacht.</p>
                <p class="mt-4 max-w-sm text-stone-300">Geen wachtlijst en geen verrassingen op de bon. Jouw moment, precies zoals afgesproken.</p>
            </div>
            <dl class="grid grid-cols-2 gap-4 border-t border-white/10 pt-6">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-400">Duur</dt>
                    <dd class="mt-1 text-2xl font-bold">{{ config('salon.service.duration') }} min</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-400">Tarief</dt>
                    <dd class="mt-1 text-2xl font-bold text-amber-400">€ {{ number_format(config('salon.service.price'), 0, ',', '.') }}</dd>
                </div>
            </dl>
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
