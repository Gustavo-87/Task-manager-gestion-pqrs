<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/pqrs')->assertRedirect(route('login'));
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => '12345']);
        $this->get(route('login'));
        $sessionId = session()->getId();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => '12345'])
            ->assertRedirect(route('pqrs.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, session()->getId());

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_can_login_with_an_uppercase_email(): void
    {
        $user = User::factory()->create([
            'email' => 'residente@example.com',
            'password' => '12345',
        ]);

        $this->post(route('login.store'), [
            'email' => 'RESIDENTE@EXAMPLE.COM',
            'password' => '12345',
        ])->assertRedirect(route('pqrs.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_outer_spaces_in_the_email(): void
    {
        $user = User::factory()->create([
            'email' => 'residente@example.com',
            'password' => '12345',
        ]);

        $this->post(route('login.store'), [
            'email' => '  residente@example.com  ',
            'password' => '12345',
        ])->assertRedirect(route('pqrs.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_outer_spaces_in_the_password(): void
    {
        $user = User::factory()->create(['password' => ' 12345 ']);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => ' 12345 ',
        ])->assertRedirect(route('pqrs.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create(['password' => '12345']);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'incorrecta'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_request_a_password_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_user_can_reset_their_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $response = $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'NuevaClave123',
                'password_confirmation' => 'NuevaClave123',
            ]);

            $response->assertRedirect(route('login'))->assertSessionHasNoErrors();

            return Hash::check('NuevaClave123', $user->fresh()->password);
        });
    }
}
