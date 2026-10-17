<x-layouts.app title="Contact">
    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">Contact</p>
            <h1 class="mt-2 text-4xl font-bold text-stone-900">Kom langs of stuur een bericht</h1>
            <p class="mt-4 max-w-2xl text-lg text-stone-600">
                Vragen over een afspraak, of wil je iets anders weten? Bel, mail of gebruik het formulier. We reageren meestal binnen één werkdag.
            </p>
        </div>
    </section>

    <section class="mx-auto grid max-w-6xl gap-12 px-4 py-16 sm:px-6 md:grid-cols-5">
        <div class="space-y-8 md:col-span-2">
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-stone-900">Gegevens</h2>
                <dl class="mt-4 space-y-3 text-sm text-stone-700">
                    <div>
                        <dt class="font-semibold text-stone-900">Adres</dt>
                        <dd>{{ config('salon.address') }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-stone-900">Telefoon</dt>
                        <dd><a href="tel:{{ preg_replace('/\D+/', '', config('salon.phone')) }}" class="hover:text-amber-600">{{ config('salon.phone') }}</a></dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-stone-900">E-mail</dt>
                        <dd><a href="mailto:{{ config('salon.email') }}" class="hover:text-amber-600">{{ config('salon.email') }}</a></dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-stone-900">Openingstijden</h2>
                <x-opening-hours class="mt-4 text-sm text-stone-700" />
                <p class="mt-4 text-xs text-stone-500">We werken uitsluitend op afspraak.</p>
            </div>
        </div>

        <div class="md:col-span-3">
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-lg font-bold text-stone-900">Stuur ons een bericht</h2>

                @if (session('status'))
                    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.send') }}" class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-stone-700">Naam</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required
                            class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('name') border-red-400 @enderror">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-stone-700">E-mailadres</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required
                            class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('email') border-red-400 @enderror">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="message" class="block text-sm font-medium text-stone-700">Bericht</label>
                        <textarea id="message" name="message" rows="5" required
                            class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('message') border-red-400 @enderror">{{ old('message') }}</textarea>
                        @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-amber-500 px-6 py-3 font-semibold text-white shadow hover:bg-amber-400">
                            Versturen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-layouts.app>
