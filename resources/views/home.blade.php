{{-- Homepage. De menubalk en footer komen uit components/layouts/app.blade.php --}}
<x-layouts.app>
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-stone-900 text-white">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(245,158,11,0.25),_transparent_60%)]"></div>
        <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-4 py-20 sm:px-6 md:grid-cols-2 md:py-28">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-amber-400">Welkom bij {{ config('salon.name') }}</p>
                <h1 class="mt-3 text-4xl font-bold leading-tight sm:text-5xl">
                    Strak geknipt,<br>zonder wachten.
                </h1>
                <p class="mt-5 max-w-lg text-lg text-stone-300">
                    Boek je knipbeurt online in minder dan een minuut. Kies een dag en tijd die jou past en loop op tijd naar binnen — geen wachtrij, geen gedoe.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('appointment') }}" class="inline-flex items-center rounded-lg bg-amber-500 px-6 py-3 font-semibold text-white shadow-lg hover:bg-amber-400">
                        Afspraak maken
                    </a>
                    <a href="{{ route('about') }}" class="inline-flex items-center rounded-lg border border-stone-600 px-6 py-3 font-semibold text-stone-100 hover:bg-stone-800">
                        Meer over ons
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-stone-700 bg-stone-800/60 p-6 shadow-xl backdrop-blur">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Onze dienst</h2>
                    <span class="rounded-full bg-amber-500/20 px-3 py-1 text-xs font-semibold text-amber-300">Betalen in de salon</span>
                </div>
                <div class="mt-4 flex items-center justify-between rounded-xl bg-stone-900 p-5">
                    <div>
                        <p class="text-xl font-bold">{{ config('salon.service.name') }}</p>
                        <p class="text-sm text-stone-400">Wassen, knippen en stylen · {{ config('salon.service.duration') }} min</p>
                    </div>
                    <p class="text-2xl font-bold text-amber-400">€ {{ number_format(config('salon.service.price'), 2, ',', '.') }}</p>
                </div>
                <div class="mt-5">
                    <p class="text-sm font-semibold text-stone-300">Openingstijden</p>
                    <x-opening-hours class="mt-2 text-sm text-stone-400" />
                </div>
            </div>
        </div>
    </section>

    {{-- USP's --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <div class="grid gap-8 md:grid-cols-3">
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <h3 class="mt-4 text-lg font-bold text-stone-900">Altijd op tijd</h3>
                <p class="mt-2 text-sm text-stone-600">Met een afspraak ben je direct aan de beurt. Geen volle wachtkamer, wel een kop koffie.</p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                </div>
                <h3 class="mt-4 text-lg font-bold text-stone-900">Vakmanschap</h3>
                <p class="mt-2 text-sm text-stone-600">Meer dan 15 jaar ervaring met klassieke en moderne kapsels. Jij zegt het, wij knippen het.</p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                </div>
                <h3 class="mt-4 text-lg font-bold text-stone-900">Gewoon in de buurt</h3>
                <p class="mt-2 text-sm text-stone-600">Je vindt ons aan de {{ config('salon.address') }}. Gratis parkeren voor de deur.</p>
            </div>
        </div>
    </section>

    <section class="border-y border-stone-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">Zo werkt het</p>
            <h2 class="mt-2 text-3xl font-bold text-stone-900">In drie stappen klaar</h2>
            <ol class="mt-8 grid gap-6 md:grid-cols-3">
                <li class="rounded-2xl border border-stone-200 bg-stone-50 p-6">
                    <p class="text-sm font-bold text-amber-600">01</p>
                    <h3 class="mt-3 text-lg font-bold text-stone-900">Kies een dag</h3>
                    <p class="mt-2 text-sm text-stone-600">Dinsdag tot en met zaterdag, tot {{ config('salon.booking_window_days') }} dagen vooruit.</p>
                </li>
                <li class="rounded-2xl border border-stone-200 bg-stone-50 p-6">
                    <p class="text-sm font-bold text-amber-600">02</p>
                    <h3 class="mt-3 text-lg font-bold text-stone-900">Kies een tijd</h3>
                    <p class="mt-2 text-sm text-stone-600">Elke knipbeurt duurt {{ config('salon.service.duration') }} minuten. Bezette tijden zie je niet.</p>
                </li>
                <li class="rounded-2xl border border-stone-200 bg-stone-50 p-6">
                    <p class="text-sm font-bold text-amber-600">03</p>
                    <h3 class="mt-3 text-lg font-bold text-stone-900">Kom langs</h3>
                    <p class="mt-2 text-sm text-stone-600">Je krijgt een e-mail ter bevestiging. Betalen doe je in de salon.</p>
                </li>
            </ol>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-amber-500">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-6 px-4 py-12 sm:px-6 md:flex-row">
            <div>
                <h2 class="text-2xl font-bold text-white">Klaar voor een frisse coupe?</h2>
                <p class="mt-1 text-amber-50">Kies je moment, wij zorgen voor de rest.</p>
            </div>
            <a href="{{ route('appointment') }}" class="inline-flex items-center rounded-lg bg-stone-900 px-6 py-3 font-semibold text-white shadow hover:bg-stone-800">
                Nu een afspraak maken
            </a>
        </div>
    </section>
</x-layouts.app>
