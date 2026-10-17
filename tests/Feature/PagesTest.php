<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('Afspraak maken');
        $this->get(route('about'))->assertOk()->assertSee('Over ons');
        $this->get(route('contact'))->assertOk()->assertSee('Stuur ons een bericht');
        $this->get(route('appointment'))->assertOk()->assertSeeLivewire('appointment-booking');
    }

    public function test_contact_form_validates_and_sends(): void
    {
        $this->post(route('contact.send'), [])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->post(route('contact.send'), [
            'name' => 'Test Persoon',
            'email' => 'test@example.com',
            'message' => 'Dit is een testbericht van minimaal tien tekens.',
        ])->assertRedirect(route('contact'))->assertSessionHas('status');
    }
}
