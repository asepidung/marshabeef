<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PinAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('pin-login|127.0.0.1');
    }

    private function loginWithPin(string $pin = '2468'): void
    {
        config(['access.pin' => $pin]);
        $this->post(route('login.attempt'), ['pin' => $pin])->assertRedirect(route('labels.create'));
    }

    public function test_without_pin_outside_production_the_app_is_open(): void
    {
        config(['access.pin' => null]);

        $this->get(route('labels.create'))->assertOk();
        $this->get('/')->assertOk()->assertSee('Klik layar untuk masuk')->assertDontSee('name="pin"', false);
    }

    public function test_dashboard_shows_the_pin_field_instead_of_click_to_start(): void
    {
        config(['access.pin' => '2468']);

        $this->get('/')->assertOk()->assertSee('name="pin"', false)->assertDontSee('Klik layar untuk masuk');
    }

    public function test_with_pin_guests_are_sent_to_the_dashboard(): void
    {
        config(['access.pin' => '2468']);

        foreach (['labels.create', 'products.index', 'types.index', 'keepalive'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $this->post(route('labels.store'), [])->assertRedirect(route('login'));
        $this->assertSame('/', route('login', [], false));
    }

    public function test_wrong_pin_is_rejected(): void
    {
        config(['access.pin' => '2468']);

        $this->post(route('login.attempt'), ['pin' => '0000'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['pin' => 'PIN salah.']);

        $this->get(route('labels.create'))->assertRedirect(route('login'));
    }

    public function test_correct_pin_goes_straight_to_the_label_page_whatever_was_requested(): void
    {
        config(['access.pin' => '2468']);

        $this->get(route('products.index'))->assertRedirect(route('login'));
        $this->post(route('login.attempt'), ['pin' => '2468'])->assertRedirect(route('labels.create'));

        $this->get(route('labels.create'))->assertOk();
        $this->get(route('products.index'))->assertOk();
    }

    public function test_logged_in_user_visiting_the_dashboard_goes_to_the_label_page(): void
    {
        $this->loginWithPin();

        $this->get('/')->assertRedirect(route('labels.create'));
    }

    public function test_logout_closes_access_again(): void
    {
        $this->loginWithPin();

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->get(route('labels.create'))->assertRedirect(route('login'));
    }

    public function test_session_locks_after_thirty_minutes_without_activity(): void
    {
        config(['access.idle_minutes' => 30]);
        $this->loginWithPin();

        $this->travel(29)->minutes();
        $this->get(route('labels.create'))->assertOk();

        $this->travel(31)->minutes();
        $this->get(route('labels.create'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->get(route('labels.create'))->assertRedirect(route('login'));
        $this->get('/')->assertOk()->assertSee('name="pin"', false);
    }

    public function test_each_request_resets_the_idle_timer(): void
    {
        config(['access.idle_minutes' => 30]);
        $this->loginWithPin();

        foreach (range(1, 4) as $i) {
            $this->travel(20)->minutes();
            $this->get(route('labels.create'))->assertOk();
        }
    }

    public function test_keepalive_counts_as_activity(): void
    {
        config(['access.idle_minutes' => 30]);
        $this->loginWithPin();

        $this->travel(20)->minutes();
        $this->get(route('keepalive'))->assertNoContent();
        $this->travel(20)->minutes();

        $this->get(route('labels.create'))->assertOk();
    }

    public function test_lock_url_ends_the_session_and_shows_a_message_on_the_dashboard(): void
    {
        $this->loginWithPin();

        $this->get(route('lock'))->assertRedirect(route('login'))->assertSessionHas('status');
        $this->get(route('labels.create'))->assertRedirect(route('login'));
    }

    public function test_page_only_sets_up_the_idle_lock_when_a_pin_is_configured(): void
    {
        config(['access.pin' => null]);
        $this->get(route('labels.create'))->assertDontSee('data-idle-minutes', false);

        config(['access.pin' => '2468', 'access.idle_minutes' => 30]);
        $this->loginWithPin();
        $this->get(route('labels.create'))->assertSee('data-idle-minutes="30"', false);
    }

    public function test_production_without_pin_is_locked_and_explains_why(): void
    {
        config(['access.pin' => null]);
        $this->app['env'] = 'production';

        $this->get(route('labels.create'))->assertRedirect(route('login'));
        $this->get('/')->assertOk()->assertSee('APP_PIN')->assertDontSee('name="pin"', false);

        // Di production CSRF aktif, jadi sertakan token agar request sampai ke logika PIN.
        $this->withSession(['_token' => 'tes'])
            ->post(route('login.attempt'), ['_token' => 'tes', 'pin' => '1234'])
            ->assertSessionHasErrors(['pin' => 'PIN belum diatur. Isi APP_PIN di file .env.']);
    }

    public function test_login_attempts_are_rate_limited_with_a_friendly_message(): void
    {
        config(['access.pin' => '2468']);

        foreach (range(1, 5) as $i) {
            $this->post(route('login.attempt'), ['pin' => '0000'])->assertSessionHasErrors('pin');
        }

        // Percobaan ke-6 ditolak walau PIN-nya benar, dan tanpa halaman error 429.
        $this->post(route('login.attempt'), ['pin' => '2468'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('pin');
        $this->assertStringContainsString('Terlalu banyak percobaan', session('errors')->first('pin'));
        $this->get(route('labels.create'))->assertRedirect(route('login'));
    }

    public function test_expired_csrf_token_returns_to_the_dashboard_instead_of_a_419_page(): void
    {
        config(['access.pin' => '2468']);
        $this->app['env'] = 'production';

        $this->post(route('login.attempt'), ['pin' => '2468'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');
    }

    public function test_old_login_url_still_works(): void
    {
        $this->get('/login')->assertRedirect(route('login'));
    }

    public function test_health_check_stays_public(): void
    {
        config(['access.pin' => '2468']);

        $this->get('/up')->assertOk();
        $this->assertSame(0, Product::count());
    }
}
