<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        RateLimiter::clear('user@example.com|127.0.0.1');
        parent::tearDown();
    }

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    */

    public function test_a_visitor_can_register(): void
    {
        $this->post(route('register'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $user = User::firstWhere('email', 'ada@example.com');
        $this->assertNotNull($user);
        // Hashed by the `password` cast, never stored in the clear.
        $this->assertNotSame('correct-horse-battery', $user->password);
        $this->assertTrue(Hash::check('correct-horse-battery', $user->password));
    }

    public function test_registration_validates_its_input_and_shows_errors(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->from(route('register'))
            ->post(route('register'), [
                'name' => '',
                'email' => 'taken@example.com',
                'password' => 'short',
                'password_confirmation' => 'mismatch',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest();
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function test_a_user_can_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Registration complexity rules must not be applied at login: a short but
     * correct password has to authenticate, not fail validation.
     */
    public function test_a_short_but_correct_password_still_logs_in(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'abc123',
        ]);

        $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'abc123',
        ])->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_wrong_password_reports_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'user@example.com', 'password' => 'password']);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'user@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_repeated_failures_are_rate_limited(): void
    {
        User::factory()->create(['email' => 'user@example.com', 'password' => 'password']);

        // Five failures are allowed...
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email' => 'user@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // ...the sixth is locked out, and the correct password no longer works.
        $response = $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first('email')
        );
        $this->assertGuest();
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function test_logging_out_ends_the_session_and_rotates_the_csrf_token(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->get(route('dashboard'))->assertOk();

        $tokenBefore = session()->token();

        $this->delete(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
        // Session::invalidate() + regenerateToken() means the old token is gone.
        $this->assertNotSame($tokenBefore, session()->token());
    }

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    */

    public function test_guests_see_the_landing_page_and_users_are_sent_to_the_dashboard(): void
    {
        $this->get('/')->assertOk()->assertSee('Capture the idea');

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }

    public function test_authenticated_users_cannot_reach_the_guest_pages(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('login'))->assertRedirect(route('dashboard'));
        $this->get(route('register'))->assertRedirect(route('dashboard'));
    }
}
