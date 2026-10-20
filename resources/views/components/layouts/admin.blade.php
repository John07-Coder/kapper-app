{{--
    Layout alleen voor het dashboard.
    De website voor klanten gebruikt components/layouts/app.blade.php.
--}}
<!DOCTYPE html>
<html lang="nl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard – {{ config('salon.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full bg-stone-100 font-sans text-stone-800 antialiased selection:bg-amber-200 selection:text-stone-900">
    <header class="bg-stone-900 text-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500 font-bold text-white">K</span>
                <span>
                    <span class="block text-xs font-semibold uppercase tracking-wide text-amber-400">Beheer</span>
                    <span class="block font-bold">{{ config('salon.name') }}</span>
                </span>
            </a>

            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('home') }}" class="hidden text-stone-300 hover:text-white sm:inline">Naar de website</a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-white/10 px-3 py-2 font-medium hover:bg-white/20">
                        Uitloggen
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
