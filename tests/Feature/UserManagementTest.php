<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_create_and_update_users(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->get(route('users.index'))->assertOk();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Residente Uno',
            'email' => 'residente1@example.com',
            'rol' => 'residente',
            'password' => 'Password2026!',
            'password_confirmation' => 'Password2026!',
        ])->assertRedirect(route('users.index'));

        $resident = User::where('email', 'residente1@example.com')->firstOrFail();

        $this->actingAs($admin)->put(route('users.update', $resident), [
            'name' => 'Residente Actualizado',
            'email' => $resident->email,
            'rol' => 'residente',
            'activo' => '0',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $resident->id,
            'name' => 'Residente Actualizado',
            'activo' => false,
        ]);
    }

    public function test_resident_cannot_access_user_management(): void
    {
        $resident = User::factory()->create(['rol' => 'residente']);

        $this->actingAs($resident)->get(route('users.index'))->assertForbidden();
        $this->actingAs($resident)->post(route('users.store'), [])->assertForbidden();
    }

    public function test_admin_cannot_deactivate_demote_or_delete_self(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->put(route('users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'rol' => 'residente',
            'activo' => '0',
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasErrors('activo');

        $this->actingAs($admin)->delete(route('users.destroy', $admin))->assertSessionHasErrors('usuario');

        $admin->refresh();
        $this->assertSame('admin', $admin->rol);
        $this->assertTrue($admin->activo);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create(['activo' => false]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_reset_password_and_delete_another_user(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $resident = User::factory()->create(['rol' => 'residente']);

        $this->actingAs($admin)->put(route('users.update', $resident), [
            'name' => $resident->name,
            'email' => $resident->email,
            'rol' => 'residente',
            'activo' => '1',
            'password' => 'NuevaClave2026!',
            'password_confirmation' => 'NuevaClave2026!',
        ])->assertRedirect(route('users.index'));

        $this->assertTrue(password_verify('NuevaClave2026!', $resident->fresh()->password));

        $this->actingAs($admin)->delete(route('users.destroy', $resident))->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $resident->id]);
    }
}
