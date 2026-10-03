<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PinAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_without_pin_outside_production_the_app_is_open(): void
    {
        config(['access.pin' => null]);

        $this->get(route('labels.create'))->assertOk();
    }

    public function test_with_pin_guests_are_sent_to_login(): void
    {
        config(['access.pin' => '2468']);

        foreach (['labels.create', 'products.index', 'types.index'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $this->post(route('labels.store'), [])->assertRedirect(route('login'));
    }

    public function test_wrong_pin_is_rejected(): void
    {
        config(['access.pin' => '2468']);

        $this->from(route('login'))->post(route('login.attempt'), ['pin' => '0000'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('pin');

        $this->get(route('labels.create'))->assertRedirect(route('login'));
    }

    public function test_correct_pin_opens_the_app_and_returns_to_requested_page(): void
    {
        config(['access.pin' => '2468']);

        $this->get(route('products.index'))->assertRedirect(route('login'));
        $this->post(route('login.attempt'), ['pin' => '2468'])->assertRedirect(route('products.index'));

        $this->get(route('products.index'))->assertOk();
    }

    public function test_logout_closes_access_again(): void
    {
        config(['access.pin' => '2468']);

        $this->post(route('login.attempt'), ['pin' => '2468']);
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->get(route('labels.create'))->assertRedirect(route('login'));
    }

    public function test_production_without_pin_is_locked_and_explains_why(): void
    {
        config(['access.pin' => null]);
        $this->app['env'] = 'production';

        $this->get(route('labels.create'))->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk()->assertSee('APP_PIN');

        // Di production CSRF aktif, jadi sertakan token agar request sampai ke logika PIN.
        $this->withSession(['_token' => 'tes'])
            ->post(route('login.attempt'), ['_token' => 'tes', 'pin' => '1234'])
            ->assertSessionHasErrors(['pin' => 'PIN belum diatur. Isi APP_PIN di file .env.']);
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        config(['access.pin' => '2468']);

        foreach (range(1, 5) as $i) {
            $this->post(route('login.attempt'), ['pin' => '0000'])->assertSessionHasErrors('pin');
        }

        $this->post(route('login.attempt'), ['pin' => '2468'])->assertStatus(429);
    }

    public function test_welcome_page_and_health_check_stay_public(): void
    {
        config(['access.pin' => '2468']);

        $this->get('/')->assertOk();
        $this->get('/up')->assertOk();
    }
}
