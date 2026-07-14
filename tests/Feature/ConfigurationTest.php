<?php

namespace Tests\Feature;

use App\Mail\ConfigurationTestMail;
use App\Models\AppSetting;
use App\Models\Pqr;
use App\Models\TipoPqr;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_access_configuration(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $resident = User::factory()->create(['rol' => 'residente']);

        $this->actingAs($admin)->get(route('configuration.index'))->assertOk()->assertSee('Datos del conjunto');
        $this->actingAs($resident)->get(route('configuration.index'))->assertForbidden();
        $this->actingAs($resident)->put(route('configuration.update'), [])->assertForbidden();
    }

    public function test_admin_can_update_residential_information_and_change_is_audited(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->put(route('configuration.update'), [
            'residential_name' => 'Conjunto Los Pinos',
            'nit' => '900.123.456-7',
            'address' => 'Calle 10 # 20-30',
            'phone' => '3001234567',
            'email' => 'administracion@pinos.test',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame('Conjunto Los Pinos', AppSetting::current()->residential_name);
        $this->assertDatabaseHas('audits', ['module' => 'Configuración', 'action' => 'actualizar', 'user_id' => $admin->id]);
    }

    public function test_admin_can_create_and_deactivate_category(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post(route('categories.store'), [
            'nombre' => 'Convivencia',
            'descripcion' => 'Situaciones relacionadas con convivencia.',
        ])->assertRedirect(route('configuration.index'));

        $category = TipoPqr::where('nombre', 'Convivencia')->firstOrFail();
        $this->actingAs($admin)->put(route('categories.update', $category), [
            'nombre' => $category->nombre,
            'descripcion' => $category->descripcion,
            'activo' => '0',
        ])->assertRedirect(route('configuration.index'));

        $this->assertFalse($category->fresh()->activo);
        $this->assertDatabaseHas('audits', ['module' => 'Categorías', 'action' => 'crear']);
        $this->assertDatabaseHas('audits', ['module' => 'Categorías', 'action' => 'actualizar']);
    }

    public function test_inactive_category_cannot_be_used_for_new_pqr(): void
    {
        $resident = User::factory()->create(['rol' => 'residente']);
        $category = TipoPqr::factory()->create(['activo' => false]);

        $this->actingAs($resident)->post(route('pqrs.store'), [
            'asunto' => 'Solicitud con categoría inactiva',
            'descripcion' => 'Esta solicitud no debe ser creada.',
            'fecha_radicacion' => '2026-07-14',
            'tipo_pqr_id' => $category->id,
        ])->assertSessionHasErrors('tipo_pqr_id');
    }

    public function test_category_with_pqrs_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $category = TipoPqr::factory()->create();
        Pqr::factory()->create(['tipo_pqr_id' => $category->id]);

        $this->actingAs($admin)->delete(route('categories.destroy', $category))->assertSessionHasErrors('category');
        $this->assertDatabaseHas('tipo_pqrs', ['id' => $category->id]);
    }

    public function test_admin_can_send_configuration_test_email(): void
    {
        Mail::fake();
        config()->set('mail.default', 'smtp');
        config()->set('mail.mailers.smtp.host', 'smtp.gmail.com');
        config()->set('mail.mailers.smtp.username', 'admin@example.com');
        config()->set('mail.mailers.smtp.password', 'app-password');
        config()->set('mail.from.address', 'admin@example.com');
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post(route('configuration.test-email'))->assertRedirect()->assertSessionHas('success');

        Mail::assertSent(ConfigurationTestMail::class, fn ($mail) => $mail->hasTo($admin->email));
        $this->assertDatabaseHas('audits', ['module' => 'Configuración', 'action' => 'probar_correo']);
    }
}
