<div class="mx-auto max-w-3xl">
    {{-- $booked is gevuld nadat book() is gelukt. Dan tonen we alleen dit scherm. --}}
    @if ($booked)
        {{-- Bevestiging --}}
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-8 text-center shadow-sm">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </div>
            <h2 class="mt-4 text-2xl font-bold text-stone-900">Je afspraak is aangevraagd!</h2>
            <p class="mt-2 text-stone-600">
                Bedankt {{ $booked->name }}. We hebben een bevestiging gestuurd naar
                <span class="font-medium text-stone-900">{{ $booked->email }}</span>.
            </p>

            <dl class="mx-auto mt-6 grid max-w-md grid-cols-1 gap-3 rounded-xl bg-white p-5 text-left shadow-sm sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-500">Datum</dt>
                    <dd class="font-semibold text-stone-900">{{ $booked->date->translatedFormat('l j F Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-500">Tijd</dt>
                    <dd class="font-semibold text-stone-900">{{ $booked->startLabel() }} – {{ $booked->endLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-500">Behandeling</dt>
                    <dd class="font-semibold text-stone-900">{{ config('salon.service.name') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-500">Prijs</dt>
                    <dd class="font-semibold text-stone-900">€ {{ number_format(config('salon.service.price'), 2, ',', '.') }} <span class="text-xs font-normal text-stone-500">(betalen in de salon)</span></dd>
                </div>
            </dl>

            <p class="mt-6 text-sm text-stone-500">
                Wil je de afspraak wijzigen of annuleren? Bel ons op {{ config('salon.phone') }}.
            </p>

            <button type="button" wire:click="bookAnother"
                class="mt-6 inline-flex items-center rounded-lg bg-stone-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-stone-700">
                Nog een afspraak maken
            </button>
        </div>
    @else
        <form wire:submit="book" class="space-y-8">
            {{-- Stap 1: datum --}}
            <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-3 text-lg font-bold text-stone-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-500 text-sm text-white">1</span>
                    Kies een datum
                </h2>
                <p class="mt-1 text-sm text-stone-500">
                    We zijn open van dinsdag t/m zaterdag. Je kunt tot {{ config('salon.booking_window_days') }} dagen vooruit boeken.
                </p>

                <div class="mt-4 flex gap-2 overflow-x-auto pb-2">
                    @foreach ($dates as $d)
                        <button type="button"
                            wire:click="selectDate('{{ $d['value'] }}')"
                            wire:key="date-{{ $d['value'] }}"
                            @disabled(! $d['hasSlots'])
                            @class([
                                'flex w-16 shrink-0 flex-col items-center rounded-xl border px-2 py-3 text-sm transition',
                                'border-amber-500 bg-amber-500 text-white shadow' => $date === $d['value'],
                                'border-stone-200 bg-white text-stone-700 hover:border-amber-400' => $date !== $d['value'] && $d['hasSlots'],
                                'cursor-not-allowed border-stone-100 bg-stone-50 text-stone-300 line-through' => ! $d['hasSlots'],
                            ])>
                            <span class="text-xs uppercase">{{ $d['day'] }}</span>
                            <span class="text-xl font-bold leading-tight">{{ $d['number'] }}</span>
                            <span class="text-xs">{{ $d['month'] }}</span>
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- Stap 2: tijd --}}
            <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-3 text-lg font-bold text-stone-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-500 text-sm text-white">2</span>
                    Kies een tijd
                </h2>
                <p class="mt-1 text-sm text-stone-500">
                    Beschikbare tijden op <span class="font-medium text-stone-800">{{ $dateLabel }}</span>.
                    Een afspraak duurt {{ config('salon.service.duration') }} minuten.
                </p>

                <div wire:loading.class="opacity-50" wire:target="selectDate" class="mt-4">
                    @if (count($times) === 0)
                        <p class="rounded-lg bg-stone-50 p-4 text-sm text-stone-500">
                            Geen vrije tijden meer op deze dag. Kies een andere datum.
                        </p>
                    @else
                        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-6">
                            @foreach ($times as $slot)
                                <button type="button"
                                    wire:click="selectTime('{{ $slot }}')"
                                    wire:key="slot-{{ $date }}-{{ $slot }}"
                                    @class([
                                        'rounded-lg border px-3 py-2 text-sm font-medium transition',
                                        'border-amber-500 bg-amber-500 text-white shadow' => $time === $slot,
                                        'border-stone-200 bg-white text-stone-700 hover:border-amber-400' => $time !== $slot,
                                    ])>
                                    {{ $slot }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @error('time')
                    <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </section>

            {{-- Stap 3: gegevens --}}
            <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-3 text-lg font-bold text-stone-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-500 text-sm text-white">3</span>
                    Jouw gegevens
                </h2>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-stone-700">Naam</label>
                        <input id="name" type="text" wire:model.blur="name" autocomplete="name"
                            class="mt-1 block w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-stone-900 shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('name') border-red-400 @enderror">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-stone-700">Telefoonnummer</label>
                        <input id="phone" type="tel" wire:model.blur="phone" autocomplete="tel"
                            class="mt-1 block w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-stone-900 shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('phone') border-red-400 @enderror">
                        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="email" class="block text-sm font-medium text-stone-700">E-mailadres</label>
                        <input id="email" type="email" wire:model.blur="email" autocomplete="email"
                            class="mt-1 block w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-stone-900 shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('email') border-red-400 @enderror">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-stone-700">Opmerkingen <span class="text-stone-400">(optioneel)</span></label>
                        <textarea id="notes" rows="3" wire:model.blur="notes" placeholder="Bijv. gewenste kapsel of iets wat we moeten weten"
                            class="mt-1 block w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-stone-900 shadow-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
                        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Samenvatting + verzenden --}}
            <div class="flex flex-col items-start justify-between gap-4 rounded-2xl bg-stone-900 p-6 text-white sm:flex-row sm:items-center">
                <div>
                    <p class="text-sm text-stone-300">Jouw afspraak</p>
                    <p class="text-lg font-semibold">
                        {{ config('salon.service.name') }} · {{ $dateLabel }}
                        @if ($time !== '') om {{ $time }} @else <span class="text-stone-400">– kies een tijd</span> @endif
                    </p>
                    <p class="text-sm text-stone-300">€ {{ number_format(config('salon.service.price'), 2, ',', '.') }}, te betalen in de salon.</p>
                </div>
                <button type="submit" wire:loading.attr="disabled" wire:target="book"
                    class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-6 py-3 font-semibold text-white shadow hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-60">
                    <span wire:loading.remove wire:target="book">Afspraak bevestigen</span>
                    <span wire:loading wire:target="book">Bezig met boeken…</span>
                </button>
            </div>
        </form>
    @endif
</div>
