<!DOCTYPE html>
<html lang="nl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' – ' : '' }}{{ config('salon.name') }}</title>
    <meta name="description" content="{{ $description ?? config('salon.name').' – jouw lokale kapper. Maak eenvoudig online een afspraak.' }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-full flex-col bg-stone-50 font-sans text-stone-800 antialiased">
    {{-- Menu. Op een telefoon opent <details> het menu. Op een groot scherm staat het menu altijd open. --}}
    <header class="sticky top-0 z-40 border-b border-stone-200 bg-white/90 backdrop-blur">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold text-stone-900">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500 font-bold text-white">K</span>
                {{ config('salon.name') }}
            </a>

            <ul class="hidden items-center gap-6 text-sm font-medium md:flex">
                <x-nav-link :href="route('home')" :active="request()->routeIs('home')">Home</x-nav-link>
                <x-nav-link :href="route('about')" :active="request()->routeIs('about')">Over ons</x-nav-link>
                <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">Contact</x-nav-link>
                <li>
                    <a href="{{ route('appointment') }}" class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-white shadow hover:bg-amber-400">
                        Afspraak maken
                    </a>
                </li>
            </ul>

            <details class="relative md:hidden">
                <summary class="cursor-pointer list-none rounded-lg px-3 py-2 text-sm font-medium text-stone-700 hover:bg-stone-100">Menu</summary>
                <ul class="absolute right-0 z-50 mt-2 w-52 space-y-3 rounded-xl border border-stone-200 bg-white p-4 text-sm font-medium shadow-lg">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">Home</x-nav-link>
                    <x-nav-link :href="route('about')" :active="request()->routeIs('about')">Over ons</x-nav-link>
                    <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">Contact</x-nav-link>
                    <li>
                        <a href="{{ route('appointment') }}" class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-white">Afspraak maken</a>
                    </li>
                </ul>
            </details>
        </nav>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="border-t border-stone-200 bg-white">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-3">
            <div>
                <p class="text-lg font-bold text-stone-900">{{ config('salon.name') }}</p>
                <p class="mt-2 text-sm text-stone-600">Jouw lokale kapper voor een strakke knipbeurt. Gewoon goed, zonder poespas.</p>
            </div>
            <div>
                <p class="font-semibold text-stone-900">Contact</p>
                <ul class="mt-2 space-y-1 text-sm text-stone-600">
                    <li>{{ config('salon.address') }}</li>
                    <li><a href="tel:{{ preg_replace('/\D+/', '', config('salon.phone')) }}" class="hover:text-amber-600">{{ config('salon.phone') }}</a></li>
                    <li><a href="mailto:{{ config('salon.email') }}" class="hover:text-amber-600">{{ config('salon.email') }}</a></li>
                </ul>
            </div>
            <div>
                <p class="font-semibold text-stone-900">Openingstijden</p>
                <x-opening-hours class="mt-2 text-sm text-stone-600" />
            </div>
        </div>
        <div class="border-t border-stone-100 py-4 text-center text-xs text-stone-400">
            © {{ date('Y') }} {{ config('salon.name') }} ·
            <a href="{{ route('admin.login') }}" class="hover:text-stone-600">Beheer</a>
        </div>
    </footer>
    @livewireScripts
</body>
</html>
