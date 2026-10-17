<x-layouts.app title="Afspraak maken">
    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
            <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">Online boeken</p>
            <h1 class="mt-2 text-4xl font-bold text-stone-900">Maak een afspraak</h1>
            <p class="mt-4 max-w-2xl text-lg text-stone-600">
                Kies een datum en tijd, vul je gegevens in en je bent klaar. Je ontvangt direct een bevestiging per e-mail.
                Betalen doe je gewoon in de salon.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
        {{-- Livewire-component: app/Livewire/AppointmentBooking.php --}}
        <livewire:appointment-booking />
    </section>
</x-layouts.app>
