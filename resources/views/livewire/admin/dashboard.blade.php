<div class="space-y-6">
    {{-- Welkom --}}
    <section class="overflow-hidden rounded-3xl bg-stone-900 text-white shadow-lg">
        <div class="bg-[radial-gradient(ellipse_at_top_right,_rgba(245,158,11,0.35),_transparent_55%)] px-6 py-8 sm:px-8">
            <p class="text-sm font-semibold uppercase tracking-widest text-amber-400">{{ $greeting }}</p>
            <h1 class="mt-1 text-3xl font-bold">{{ $userName }}</h1>
            <p class="mt-2 text-stone-300">{{ $todayLabel }} · één knipbeurt duurt {{ config('salon.service.duration') }} minuten</p>
        </div>
    </section>

    {{-- Cijfers. $stats komt uit Dashboard.php --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">Afspraken vandaag</p>
            <p class="mt-2 text-4xl font-bold text-stone-900">{{ $stats['today'] }}</p>
        </article>
        <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-sm text-amber-800">Nog te bevestigen</p>
            <p class="mt-2 text-4xl font-bold text-amber-700">{{ $stats['pending'] }}</p>
        </article>
        <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">Komende afspraken</p>
            <p class="mt-2 text-4xl font-bold text-stone-900">{{ $stats['upcoming'] }}</p>
        </article>
        <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">Verwachte omzet vandaag</p>
            <p class="mt-2 text-4xl font-bold text-stone-900">€ {{ number_format($stats['revenue'], 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-stone-400">{{ $stats['today'] }} × € {{ number_format(config('salon.service.price'), 2, ',', '.') }}, betalen in de salon</p>
        </article>
    </section>

    {{-- Snel een dag kiezen --}}
    <section class="flex gap-2 overflow-x-auto pb-1">
        @foreach ($week as $day)
            <button type="button"
                wire:click="showDate('{{ $day['date'] }}')"
                @class([
                    'flex w-24 shrink-0 flex-col items-center rounded-2xl border px-2 py-3 text-sm',
                    'border-amber-500 bg-amber-500 text-white shadow' => $date === $day['date'],
                    'border-stone-200 bg-white text-stone-700 hover:border-amber-400' => $date !== $day['date'],
                ])>
                <span class="text-xs uppercase">{{ $day['name'] }}</span>
                <span class="text-2xl font-bold leading-tight">{{ $day['number'] }}</span>
                <span class="text-xs">{{ $day['month'] }}</span>
                <span class="mt-1 text-xs">{{ $day['count'] }} afspr.</span>
            </button>
        @endforeach
    </section>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Agenda van de gekozen dag --}}
        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-stone-900">Agenda</h2>
                    <p class="text-sm text-stone-500">{{ $dateLabel }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="previousDay" class="rounded-lg border border-stone-200 px-3 py-1.5 text-sm hover:bg-stone-50">Vorige</button>
                    <input type="date" wire:model.live="date" class="rounded-lg border border-stone-300 px-3 py-1.5 text-sm">
                    <button type="button" wire:click="nextDay" class="rounded-lg border border-stone-200 px-3 py-1.5 text-sm hover:bg-stone-50">Volgende</button>
                </div>
            </div>

            @if (! $isOpen)
                <p class="mt-6 rounded-xl bg-stone-50 p-4 text-sm text-stone-500">De salon is deze dag gesloten.</p>
            @else
                <ul class="mt-5 divide-y divide-stone-100">
                    @foreach ($agenda as $row)
                        <li class="flex gap-4 py-3">
                            <p class="w-14 shrink-0 pt-1 text-sm font-semibold text-stone-500">{{ $row['time'] }}</p>

                            @if ($row['appointment'])
                                @php($appointment = $row['appointment'])
                                <div class="flex flex-1 flex-wrap items-center justify-between gap-3 rounded-xl border border-stone-200 bg-stone-50 px-4 py-3">
                                    <div>
                                        <p class="font-semibold text-stone-900">{{ $appointment->name }}</p>
                                        <p class="text-sm text-stone-500">
                                            {{ $appointment->startLabel() }}–{{ $appointment->endLabel() }}
                                            · {{ $appointment->phone }}
                                        </p>
                                        @if ($appointment->notes)
                                            <p class="mt-1 text-sm text-stone-600">{{ $appointment->notes }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @include('livewire.admin.status', ['appointment' => $appointment])
                                        @if ($appointment->status === \App\Models\Appointment::STATUS_PENDING)
                                            <button type="button" wire:click="confirm({{ $appointment->id }})" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">Bevestigen</button>
                                        @endif
                                        <button type="button" wire:click="cancel({{ $appointment->id }})" wire:confirm="Deze afspraak annuleren?" class="rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100">Annuleren</button>
                                    </div>
                                </div>
                            @else
                                <div class="flex flex-1 items-center rounded-xl border border-dashed border-stone-200 px-4 py-3 text-sm text-stone-400">
                                    Vrij
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Wat de kapper nog moet doen --}}
        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold text-stone-900">Te bevestigen</h2>
            <p class="text-sm text-stone-500">Nieuwe aanvragen van klanten.</p>

            @if ($pending->isEmpty())
                <p class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800">Alles is bijgewerkt. Er staat niets open.</p>
            @else
                <ul class="mt-5 space-y-3">
                    @foreach ($pending as $appointment)
                        <li class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="font-semibold text-stone-900">{{ $appointment->name }}</p>
                            <p class="text-sm text-stone-600">
                                {{ $appointment->date->translatedFormat('D j M') }}
                                · {{ $appointment->startLabel() }}
                            </p>
                            <p class="text-sm text-stone-500">{{ $appointment->phone }}</p>
                            <div class="mt-3 flex gap-2">
                                <button type="button" wire:click="confirm({{ $appointment->id }})" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">Bevestigen</button>
                                <button type="button" wire:click="cancel({{ $appointment->id }})" wire:confirm="Deze afspraak annuleren?" class="rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-xs font-semibold text-stone-700">Annuleren</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    {{-- Volledige lijst, met filters --}}
    <section class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-stone-100 p-5 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-lg font-bold text-stone-900">Alle afspraken</h2>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="setView('day')" @class(['rounded-lg px-3 py-1.5 text-sm font-medium', 'bg-stone-900 text-white' => $view === 'day', 'bg-stone-100 text-stone-700' => $view !== 'day'])>Deze dag</button>
                <button type="button" wire:click="setView('upcoming')" @class(['rounded-lg px-3 py-1.5 text-sm font-medium', 'bg-stone-900 text-white' => $view === 'upcoming', 'bg-stone-100 text-stone-700' => $view !== 'upcoming'])>Komend</button>
                <button type="button" wire:click="setView('all')" @class(['rounded-lg px-3 py-1.5 text-sm font-medium', 'bg-stone-900 text-white' => $view === 'all', 'bg-stone-100 text-stone-700' => $view !== 'all'])>Alles</button>

                <select wire:model.live="status" class="rounded-lg border border-stone-300 px-3 py-1.5 text-sm">
                    <option value="">Alle statussen</option>
                    <option value="pending">In afwachting</option>
                    <option value="confirmed">Bevestigd</option>
                    <option value="cancelled">Geannuleerd</option>
                </select>

                {{-- live = de tabel ververst terwijl je typt --}}
                <input type="search" wire:model.live="search" placeholder="Zoek naam, e-mail of telefoon" class="w-full rounded-lg border border-stone-300 px-3 py-1.5 text-sm sm:w-64">
            </div>
        </div>

        @error('restore')
            <p class="px-5 pt-4 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Wanneer</th>
                        <th class="px-5 py-3">Klant</th>
                        <th class="px-5 py-3">Contact</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Acties</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td class="px-5 py-3">
                                <p class="font-semibold text-stone-900">{{ $appointment->startLabel() }}–{{ $appointment->endLabel() }}</p>
                                <p class="text-stone-500">{{ $appointment->date->translatedFormat('D j M Y') }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <p class="font-medium text-stone-900">{{ $appointment->name }}</p>
                                <p class="text-stone-500">{{ $appointment->notes }}</p>
                            </td>
                            <td class="px-5 py-3 text-stone-600">
                                <p>{{ $appointment->email }}</p>
                                <p>{{ $appointment->phone }}</p>
                            </td>
                            <td class="px-5 py-3">
                                @include('livewire.admin.status', ['appointment' => $appointment])
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if ($appointment->status === \App\Models\Appointment::STATUS_PENDING)
                                    <button type="button" wire:click="confirm({{ $appointment->id }})" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">Bevestigen</button>
                                @endif

                                @if ($appointment->isCancelled())
                                    <button type="button" wire:click="restore({{ $appointment->id }})" class="rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100">Herstellen</button>
                                    <button type="button" wire:click="delete({{ $appointment->id }})" wire:confirm="Deze afspraak definitief verwijderen?" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Verwijderen</button>
                                @else
                                    <button type="button" wire:click="cancel({{ $appointment->id }})" wire:confirm="Deze afspraak annuleren?" class="rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100">Annuleren</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-stone-500">Geen afspraken gevonden.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
