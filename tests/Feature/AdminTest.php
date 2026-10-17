<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-09-08 08:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.login'))->assertOk()->assertSee('Beheer inloggen');
    }

    public function test_admin_can_login_and_logout(): void
    {
        $user = User::factory()->create(['email' => 'kapper@example.com']);

        $this->post(route('admin.login.attempt'), [
            'email' => 'kapper@example.com',
            'password' => 'verkeerd',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('admin.login.attempt'), [
            'email' => 'kapper@example.com',
            'password' => 'password', // standaard wachtwoord van de UserFactory
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Afspraken')
            ->assertSeeLivewire('admin.dashboard');

        $this->post(route('admin.logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_admin_can_manage_appointments(): void
    {
        $this->actingAs(User::factory()->create());

        $appointment = Appointment::create([
            'name' => 'Klant Vandaag',
            'email' => 'vandaag@example.com',
            'phone' => '0611111111',
            'date' => '2026-09-08',
            'start_time' => '10:00',
            'end_time' => '10:30',
            'status' => Appointment::STATUS_PENDING,
        ]);

        Appointment::create([
            'name' => 'Klant Morgen',
            'email' => 'morgen@example.com',
            'phone' => '0622222222',
            'date' => '2026-09-09',
            'start_time' => '10:00',
            'end_time' => '10:30',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        // De agenda toont altijd de gekozen dag. Zoeken filtert alleen de tabel onderaan.
        $component = Livewire::test(Dashboard::class)
            ->assertSet('view', 'day')
            ->assertSet('date', '2026-09-08')
            ->assertSee('Klant Vandaag')
            ->assertDontSee('Klant Morgen')
            ->call('setView', 'upcoming')
            ->assertSee('Klant Vandaag')
            ->assertSee('Klant Morgen')
            ->set('search', 'morgen@example.com')
            ->assertSee('Klant Morgen')
            ->set('search', '');

        $component->call('confirm', $appointment->id);
        $this->assertSame(Appointment::STATUS_CONFIRMED, $appointment->fresh()->status);

        $component->call('cancel', $appointment->id);
        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);

        $component->call('restore', $appointment->id)->assertHasNoErrors();
        $this->assertSame(Appointment::STATUS_PENDING, $appointment->fresh()->status);

        $component->call('cancel', $appointment->id)->call('delete', $appointment->id);
        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
    }

    public function test_restore_fails_when_slot_is_taken(): void
    {
        $this->actingAs(User::factory()->create());

        $cancelled = Appointment::create([
            'name' => 'Geannuleerd',
            'email' => 'a@example.com',
            'phone' => '0611111111',
            'date' => '2026-09-09',
            'start_time' => '10:00',
            'end_time' => '10:30',
            'status' => Appointment::STATUS_CANCELLED,
        ]);

        Appointment::create([
            'name' => 'Nieuwe boeking',
            'email' => 'b@example.com',
            'phone' => '0622222222',
            'date' => '2026-09-09',
            'start_time' => '10:00',
            'end_time' => '10:30',
            'status' => Appointment::STATUS_PENDING,
        ]);

        Livewire::test(Dashboard::class)
            ->call('restore', $cancelled->id)
            ->assertHasErrors('restore');

        $this->assertSame(Appointment::STATUS_CANCELLED, $cancelled->fresh()->status);
    }
}
