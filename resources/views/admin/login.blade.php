<x-layouts.app title="Inloggen">
    <section class="mx-auto flex max-w-md flex-col px-4 py-20 sm:px-6">
        <div class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-bold text-stone-900">Beheer inloggen</h1>
            <p class="mt-1 text-sm text-stone-500">Alleen voor medewerkers van {{ config('salon.name') }}.</p>

            <form method="POST" action="{{ route('admin.login.attempt') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-stone-700">E-mailadres</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('email') border-red-400 @enderror">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-stone-700">Wachtwoord</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('password') border-red-400 @enderror">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-stone-600">
                    <input type="checkbox" name="remember" class="rounded border-stone-300 text-amber-500 focus:ring-amber-500">
                    Ingelogd blijven
                </label>
                <button type="submit" class="w-full rounded-lg bg-stone-900 px-4 py-2.5 font-semibold text-white hover:bg-stone-700">
                    Inloggen
                </button>
            </form>
        </div>
    </section>
</x-layouts.app>
