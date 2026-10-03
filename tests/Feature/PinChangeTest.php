<?php

namespace Tests\Feature;

use App\Support\AccessPin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PinChangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['access.pin' => '2468']);
        RateLimiter::clear('pin-change|127.0.0.1');
        RateLimiter::clear('pin-login|127.0.0.1');
    }

    private function login(string $pin = '2468'): void
    {
        $this->post(route('login.attempt'), ['pin' => $pin])->assertRedirect(route('labels.create'));
    }

    private function change(string $current, string $new, ?string $confirm = null)
    {
        return $this->post(route('pin.update'), [
            'current_pin' => $current,
            'new_pin' => $new,
            'new_pin_confirmation' => $confirm ?? $new,
        ]);
    }

    public function test_guests_cannot_open_or_submit_the_change_page(): void
    {
        $this->get(route('pin.edit'))->assertRedirect(route('login'));
        $this->post(route('pin.update'), ['current_pin' => '2468', 'new_pin' => '9999', 'new_pin_confirmation' => '9999'])
            ->assertRedirect(route('login'));

        $this->assertNull(AccessPin::storedHash());
    }

    public function test_change_page_and_menu_link_are_visible_after_login(): void
    {
        $this->login();

        $this->get(route('pin.edit'))->assertOk()->assertSee('name="current_pin"', false)->assertSee('GANTI PIN');
        $this->get(route('labels.create'))->assertSee('Ganti PIN');
    }

    public function test_pin_can_be_changed_and_only_the_new_pin_works_afterwards(): void
    {
        $this->login();

        $this->change('2468', '135790')->assertSessionHasNoErrors()->assertRedirect(route('labels.create'));

        $this->post(route('logout'));

        $this->post(route('login.attempt'), ['pin' => '2468'])->assertSessionHasErrors(['pin' => 'PIN salah.']);
        $this->login('135790');
    }

    public function test_changing_the_pin_keeps_the_current_session_logged_in_and_shows_a_toast(): void
    {
        $this->login();

        $this->change('2468', '135790');

        $this->get(route('labels.create'))->assertOk()->assertSee('PIN berhasil diganti.');
    }

    public function test_new_pin_is_stored_as_a_hash_never_as_plain_text(): void
    {
        $this->login();
        $this->change('2468', '135790');

        $stored = (string) DB::table('settings')->where('key', 'pin_hash')->value('value');

        $this->assertNotSame('', $stored);
        $this->assertStringNotContainsString('135790', $stored);
        $this->assertTrue(password_verify('135790', $stored));
    }

    public function test_wrong_current_pin_is_rejected_and_nothing_changes(): void
    {
        $this->login();

        $this->change('0000', '135790')->assertSessionHasErrors(['current_pin' => 'PIN saat ini salah.']);

        $this->assertNull(AccessPin::storedHash());
        $this->assertTrue(AccessPin::verify('2468'));
    }

    public function test_invalid_new_pins_are_rejected(): void
    {
        $this->login();

        foreach (['123', 'abcd', '12ab34', '1234567890123', '12 34', ''] as $bad) {
            $this->change('2468', $bad)->assertSessionHasErrors('new_pin');
        }

        $this->change('2468', '135790', '135791')->assertSessionHasErrors('new_pin');
        $this->change('2468', '2468')->assertSessionHasErrors(['new_pin' => 'PIN baru harus berbeda dari PIN saat ini.']);

        $this->assertNull(AccessPin::storedHash());
    }

    public function test_wrong_current_pin_attempts_are_rate_limited(): void
    {
        $this->login();

        foreach (range(1, 5) as $i) {
            $this->change('0000', '135790')->assertSessionHasErrors('current_pin');
        }

        $this->change('2468', '135790')->assertSessionHasErrors('current_pin');
        $this->assertStringContainsString('Terlalu banyak percobaan', session('errors')->first('current_pin'));
        $this->assertNull(AccessPin::storedHash());
    }

    public function test_pin_set_in_the_app_survives_without_the_env_pin(): void
    {
        $this->login();
        $this->change('2468', '135790');

        config(['access.pin' => null]);
        $this->app['env'] = 'production';

        $this->assertTrue(AccessPin::isConfigured());
        $this->assertTrue(AccessPin::verify('135790'));
        $this->assertFalse(AccessPin::verify(''));
    }

    public function test_when_no_pin_exists_yet_the_page_does_not_ask_for_a_current_pin_and_activates_protection(): void
    {
        config(['access.pin' => null]);

        $this->get(route('pin.edit'))->assertOk()->assertDontSee('name="current_pin"', false);

        $this->post(route('pin.update'), ['new_pin' => '1357', 'new_pin_confirmation' => '1357'])
            ->assertRedirect(route('labels.create'));

        $this->get(route('labels.create'))->assertOk();
        $this->post(route('logout'));
        $this->get(route('labels.create'))->assertRedirect(route('login'));
    }

    public function test_reset_command_sets_a_given_pin(): void
    {
        $this->artisan('app:reset-pin', ['pin' => '864200'])->assertSuccessful();

        $this->assertTrue(AccessPin::verify('864200'));
        $this->assertFalse(AccessPin::verify('2468'));
    }

    public function test_reset_command_without_argument_returns_to_the_initial_env_pin(): void
    {
        AccessPin::change('864200');

        $this->artisan('app:reset-pin')->assertSuccessful();

        $this->assertNull(AccessPin::storedHash());
        $this->assertTrue(AccessPin::verify('2468'));
        $this->assertFalse(AccessPin::verify('864200'));
    }

    public function test_reset_command_rejects_an_invalid_pin(): void
    {
        $this->artisan('app:reset-pin', ['pin' => '12'])->assertFailed();

        $this->assertNull(AccessPin::storedHash());
    }

    public function test_app_still_works_when_the_settings_table_does_not_exist_yet(): void
    {
        DB::statement('drop table settings');

        $this->assertNull(AccessPin::storedHash());
        $this->assertTrue(AccessPin::verify('2468'));
        $this->get('/')->assertOk();
    }
}
