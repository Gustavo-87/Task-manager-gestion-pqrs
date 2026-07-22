<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertSee('Correo electrónico')
            ->assertSee('Contraseña')
            ->assertSee('Iniciar sesión');
    }

    public function test_users_can_authenticate_using_login_and_valid_otp(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $loginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $loginResponse
            ->assertSessionHas('otp_user_id', $user->id)
            ->assertRedirect(route('otp.verify'));

        $this->assertGuest();

        $user->refresh();

        $this->assertNotNull($user->otp_code);
        $this->assertNotNull($user->otp_expires_at);

        $otpResponse = $this->post(route('otp.verify.post'), [
            'code' => $user->otp_code,
        ]);

        $this->assertAuthenticatedAs($user);

        $otpResponse->assertRedirect(
            route('dashboard', absolute: false)
        );

        $user->refresh();

        $this->assertNull($user->otp_code);
        $this->assertNull($user->otp_expires_at);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_login_validation_messages_are_in_spanish(): void
    {
        $this->post('/login', [])
            ->assertSessionHasErrors([
                'email' => 'El campo correo electrónico es obligatorio.',
                'password' => 'El campo contraseña es obligatorio.',
            ]);
    }
}
