<?php

namespace Tests\Feature;

use App\Models\Audit;
use App\Models\Pqr;
use App\Models\TipoPqr;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_view_audit_records(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $resident = User::factory()->create(['rol' => 'residente']);

        $audit = Audit::create([
            'user_id' => $admin->id,
            'module' => 'Usuarios',
            'action' => 'crear',
            'description' => 'Registro de prueba',
        ]);

        $this->actingAs($admin)
            ->get(route('audits.index'))
            ->assertOk()
            ->assertSee('Registro de prueba');

        $this->actingAs($admin)
            ->get(route('audits.show', $audit))
            ->assertOk();

        $this->actingAs($resident)
            ->get(route('audits.index'))
            ->assertForbidden();

        $this->actingAs($resident)
            ->get(route('audits.show', $audit))
            ->assertForbidden();
    }

    public function test_user_changes_are_audited_without_passwords(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Usuario Auditado',
            'email' => 'auditado@example.com',
            'rol' => 'residente',
            'password' => 'ClaveSegura2026!',
            'password_confirmation' => 'ClaveSegura2026!',
        ])->assertRedirect(route('users.index'));

        $audit = Audit::where('module', 'Usuarios')
            ->where('action', 'crear')
            ->firstOrFail();

        $this->assertSame($admin->id, $audit->user_id);
        $this->assertArrayNotHasKey('password', $audit->new_values);
        $this->assertSame('127.0.0.1', $audit->ip_address);
    }

    public function test_pqr_creation_and_response_are_audited(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $tipo = TipoPqr::factory()->create();

        $this->actingAs($admin)->post(route('pqrs.store'), [
            'asunto' => 'PQRS auditable',
            'descripcion' => 'Descripción para auditoría.',
            'fecha_radicacion' => '2026-07-14',
            'tipo_pqr_id' => $tipo->id,
        ])->assertRedirect(route('pqrs.index'));

        $pqr = Pqr::where('asunto', 'PQRS auditable')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('pqrs.workflow.transition', $pqr), ['action' => 'enviar_revision'])
            ->assertRedirect(route('pqrs.edit', $pqr));

        $this->actingAs($admin)
            ->patch(route('pqrs.workflow.transition', $pqr), ['action' => 'asignar_proceso'])
            ->assertRedirect(route('pqrs.edit', $pqr));

        $this->actingAs($admin)
            ->post(route('pqrs.respond', $pqr), ['respuesta' => 'Respuesta auditada desde la administración.'])
            ->assertRedirect(route('pqrs.edit', $pqr));

        $this->assertDatabaseHas('audits', [
            'module' => 'PQR',
            'action' => 'crear',
            'auditable_id' => $pqr->id,
        ]);

        $this->assertDatabaseHas('audits', [
            'module' => 'PQR',
            'action' => 'resolver',
            'auditable_id' => $pqr->id,
        ]);
    }

    public function test_successful_login_and_logout_are_audited(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('otp.verify'));

        $user->refresh();

        $this->post(route('otp.verify.post'), [
            'code' => $user->otp_code,
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('audits', [
            'user_id' => $user->id,
            'action' => 'iniciar_sesion',
        ]);

        $this->assertDatabaseHas('audits', [
            'user_id' => $user->id,
            'action' => 'cerrar_sesion',
        ]);
    }

    public function test_audit_has_no_mutation_routes(): void
    {
        $this->assertFalse(app('router')->has('audits.create'));
        $this->assertFalse(app('router')->has('audits.store'));
        $this->assertFalse(app('router')->has('audits.update'));
        $this->assertFalse(app('router')->has('audits.destroy'));
    }
}
