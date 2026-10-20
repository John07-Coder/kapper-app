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
        $user = User::factory()->admin()->create(['email' => 'kapper@example.com']);

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
        $this->actingAs(User::factory()->admin()->create());

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
        $this->assertSame('2026-09-08|10:00', $appointment->fresh()->active_slot);

        $component->call('cancel', $appointment->id);
        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
        $this->assertNull($appointment->fresh()->active_slot);

        $component->call('restore', $appointment->id)->assertHasNoErrors();
        $this->assertSame(Appointment::STATUS_PENDING, $appointment->fresh()->status);
        $this->assertSame('2026-09-08|10:00', $appointment->fresh()->active_slot);

        $component->call('cancel', $appointment->id)->call('delete', $appointment->id);
        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
    }

    public function test_restore_fails_when_slot_is_taken(): void
    {
        $this->actingAs(User::factory()->admin()->create());

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

    public function test_confirm_leaves_a_cancelled_appointment_unchanged(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $cancelled = Appointment::create([
            'name' => 'Geannuleerd',
            'email' => 'geannuleerd@example.com',
            'phone' => '0611111111',
            'date' => '2026-09-09',
            'start_time' => '11:00',
            'end_time' => '11:30',
            'status' => Appointment::STATUS_CANCELLED,
        ]);

        Appointment::create([
            'name' => 'Nieuwe boeking',
            'email' => 'bezet@example.com',
            'phone' => '0622222222',
            'date' => '2026-09-09',
            'start_time' => '11:00',
            'end_time' => '11:30',
            'status' => Appointment::STATUS_PENDING,
        ]);

        Livewire::test(Dashboard::class)
            ->call('confirm', $cancelled->id)
            ->assertHasNoErrors();

        $cancelled->refresh();

        $this->assertSame(Appointment::STATUS_CANCELLED, $cancelled->status);
        $this->assertNull($cancelled->active_slot);
    }

    public function test_non_admin_cannot_open_the_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'klant@example.com',
        ]);

        $this->post(route('admin.login.attempt'), [
            'email' => 'klant@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors([
            'email' => 'Dit account heeft geen toegang tot het beheer.',
        ]);
        $this->assertGuest();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        Livewire::test(Dashboard::class)->assertForbidden();
    }

    public function test_login_locks_after_five_attempts(): void
    {
        User::factory()->admin()->create(['email' => 'kapper@example.com']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.login.attempt'), [
                'email' => 'kapper@example.com',
                'password' => 'verkeerd',
            ])->assertSessionHasErrors('email');
        }

        $response = $this->post(route('admin.login.attempt'), [
            'email' => 'kapper@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Te veel inlogpogingen', session('errors')->first('email'));
        $this->assertGuest();
    }
}
