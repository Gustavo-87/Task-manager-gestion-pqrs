<?php

namespace Tests\Feature;

use App\Models\Pqr;
use App\Models\TipoPqr;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PqrTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_pqrs(): void
    {
        $user = User::factory()->create();
        $tipo = TipoPqr::factory()->create();

        $response = $this->actingAs($user)->post(route('pqrs.store'), [
            'asunto' => 'Solicitud de información',
            'descripcion' => 'Necesito conocer el estado de mi solicitud.',
            'fecha_radicacion' => '2026-07-14',
            'fecha_limite_respuesta' => '2026-07-30',
            'tipo_pqr_id' => $tipo->id,
        ]);

        $response->assertRedirect(route('pqrs.index'));
        $this->assertDatabaseHas('pqrs', [
            'asunto' => 'Solicitud de información',
            'user_id' => $user->id,
            'estado' => 'radicada',
        ]);
    }

    public function test_resident_only_sees_their_own_pqrs(): void
    {
        $resident = User::factory()->create(['rol' => 'residente']);
        $ownPqr = Pqr::factory()->create(['user_id' => $resident->id]);
        $otherPqr = Pqr::factory()->create();

        $response = $this->actingAs($resident)->get(route('pqrs.index'));

        $response->assertOk();
        $response->assertSee($ownPqr->asunto);
        $response->assertDontSee($otherPqr->asunto);
    }

    public function test_resident_cannot_view_or_modify_another_users_pqrs(): void
    {
        $resident = User::factory()->create(['rol' => 'residente']);
        $otherPqr = Pqr::factory()->create();

        $this->actingAs($resident)
            ->get(route('pqrs.edit', $otherPqr))
            ->assertForbidden();

        $this->actingAs($resident)
            ->delete(route('pqrs.destroy', $otherPqr))
            ->assertForbidden();
    }

    public function test_resident_cannot_edit_submitted_information_or_change_status(): void
    {
        $resident = User::factory()->create(['rol' => 'residente']);
        $pqr = Pqr::factory()->create([
            'user_id' => $resident->id,
            'estado' => 'radicada',
        ]);

        $this->actingAs($resident)->put(route('pqrs.update', $pqr), [
            'asunto' => 'Asunto actualizado',
            'descripcion' => $pqr->descripcion,
            'fecha_radicacion' => $pqr->fecha_radicacion->format('Y-m-d'),
            'fecha_limite_respuesta' => $pqr->fecha_limite_respuesta->format('Y-m-d'),
            'estado' => 'cerrada',
            'tipo_pqr_id' => $pqr->tipo_pqr_id,
        ])->assertForbidden();

        $this->assertSame('radicada', $pqr->fresh()->estado);
        $this->assertSame($pqr->asunto, $pqr->fresh()->asunto);
    }

    public function test_admin_can_see_and_delete_any_pqrs(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $pqr = Pqr::factory()->create();

        $this->actingAs($admin)
            ->get(route('pqrs.index'))
            ->assertOk()
            ->assertSee($pqr->asunto);

        $this->actingAs($admin)
            ->delete(route('pqrs.destroy', $pqr))
            ->assertRedirect(route('pqrs.index'));

        $this->assertDatabaseMissing('pqrs', ['id' => $pqr->id]);
    }

    public function test_admin_can_execute_the_workflow_from_radicada_to_cerrada(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $pqr = Pqr::factory()->create([
            'estado' => 'radicada',
            'respuesta' => null,
            'respondida_en' => null,
            'respondida_por' => null,
        ]);

        $this->actingAs($admin)
            ->patch(route('pqrs.workflow.transition', $pqr), ['action' => 'enviar_revision'])
            ->assertRedirect(route('pqrs.edit', $pqr));
        $this->assertSame('en_revision', $pqr->fresh()->estado);

        $this->actingAs($admin)
            ->patch(route('pqrs.workflow.transition', $pqr), ['action' => 'asignar_proceso'])
            ->assertRedirect(route('pqrs.edit', $pqr));
        $this->assertSame('en_proceso', $pqr->fresh()->estado);

        $this->actingAs($admin)
            ->post(route('pqrs.respond', $pqr), ['respuesta' => 'Caso atendido y resuelto.'])
            ->assertRedirect(route('pqrs.edit', $pqr));
        $this->assertSame('resuelta', $pqr->fresh()->estado);

        $this->actingAs($admin)
            ->patch(route('pqrs.workflow.transition', $pqr), ['action' => 'cerrar'])
            ->assertRedirect(route('pqrs.edit', $pqr));
        $this->assertSame('cerrada', $pqr->fresh()->estado);
    }

    public function test_resolving_a_pqrs_records_its_history(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $pqr = Pqr::factory()->create(['estado' => 'en_proceso']);

        $response = $this->actingAs($admin)->post(route('pqrs.respond', $pqr), [
            'respuesta' => 'Respuesta formal registrada por la administración.',
        ]);

        $response->assertRedirect(route('pqrs.edit', $pqr));
        $this->assertDatabaseHas('pqr_histories', [
            'pqr_id' => $pqr->id,
            'campo' => 'estado',
            'valor_anterior' => 'en_proceso',
            'valor_nuevo' => 'resuelta',
            'user_id' => $admin->id,
        ]);
    }

    public function test_resident_cannot_resolve_a_pqrs(): void
    {
        $resident = User::factory()->create(['rol' => 'residente']);
        $pqr = Pqr::factory()->create(['user_id' => $resident->id, 'estado' => 'en_proceso']);

        $this->actingAs($resident)
            ->post(route('pqrs.respond', $pqr), ['respuesta' => 'Respuesta no autorizada.'])
            ->assertForbidden();

        $this->assertNull($pqr->fresh()->respuesta);
    }

    public function test_admin_cannot_edit_a_pqrs_directly_through_the_update_route(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $pqr = Pqr::factory()->create(['estado' => 'en_proceso']);

        $this->actingAs($admin)->put(route('pqrs.update', $pqr), [
            'asunto' => $pqr->asunto,
            'descripcion' => $pqr->descripcion,
            'fecha_radicacion' => $pqr->fecha_radicacion->format('Y-m-d'),
            'estado' => 'resuelta',
            'tipo_pqr_id' => $pqr->tipo_pqr_id,
        ])->assertForbidden();

        $this->assertSame('en_proceso', $pqr->fresh()->estado);
        $this->assertNull($pqr->fresh()->respuesta);
    }

    public function test_admin_sees_the_pqrs_in_read_only_mode_with_workflow_actions(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $pqr = Pqr::factory()->create(['estado' => 'en_revision']);

        $this->actingAs($admin)
            ->get(route('pqrs.edit', $pqr))
            ->assertOk()
            ->assertSee('Detalle de PQRS')
            ->assertSee('La PQRS radicada por el usuario es de solo lectura.')
            ->assertSee('Flujo de atención');
    }

    public function test_admin_can_edit_an_existing_response_and_preserve_its_history(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $pqr = Pqr::factory()->create([
            'estado' => 'resuelta',
            'respuesta' => 'Respuesta inicial.',
            'respondida_en' => now()->subDay(),
            'respondida_por' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('pqrs.response.update', $pqr), ['respuesta' => 'Respuesta actualizada por una novedad.'])
            ->assertRedirect(route('pqrs.edit', $pqr));

        $this->assertDatabaseHas('pqrs', [
            'id' => $pqr->id,
            'respuesta' => 'Respuesta actualizada por una novedad.',
            'estado' => 'resuelta',
            'respondida_por' => $admin->id,
        ]);
        $this->assertDatabaseHas('pqr_histories', [
            'pqr_id' => $pqr->id,
            'campo' => 'respuesta',
            'valor_anterior' => 'Respuesta inicial.',
            'valor_nuevo' => 'Respuesta actualizada por una novedad.',
            'user_id' => $admin->id,
        ]);
    }

    public function test_resolved_pqrs_shows_the_registered_response_in_the_detail_view(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $pqr = Pqr::factory()->create([
            'estado' => 'resuelta',
            'respuesta' => 'Respuesta visible para la entrega.',
            'respondida_en' => now()->subHour(),
            'respondida_por' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('pqrs.edit', $pqr))
            ->assertOk()
            ->assertSee('Respuesta visible para la entrega.')
            ->assertSee('Editar respuesta')
            ->assertDontSee('Respuesta faltante en un registro previo');
    }

    public function test_resident_cannot_edit_an_existing_response(): void
    {
        $resident = User::factory()->create(['rol' => 'residente']);
        $pqr = Pqr::factory()->create([
            'user_id' => $resident->id,
            'estado' => 'resuelta',
            'respuesta' => 'Respuesta inicial.',
        ]);

        $this->actingAs($resident)
            ->patch(route('pqrs.response.update', $pqr), ['respuesta' => 'Cambio no autorizado.'])
            ->assertForbidden();

        $this->assertSame('Respuesta inicial.', $pqr->fresh()->respuesta);
    }

    public function test_admin_can_register_the_missing_response_on_a_legacy_resolved_pqrs(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $pqr = Pqr::factory()->create([
            'estado' => 'resuelta',
            'respuesta' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('pqrs.edit', $pqr))
            ->assertOk()
            ->assertSee('Respuesta faltante en un registro previo')
            ->assertSee('Registrar respuesta faltante')
            ->assertDontSee('Esta PQRS aún no ha recibido respuesta.');

        $this->actingAs($admin)
            ->post(route('pqrs.respond', $pqr), ['respuesta' => 'Respuesta recuperada del trámite anterior.'])
            ->assertRedirect(route('pqrs.edit', $pqr));

        $this->assertDatabaseHas('pqrs', [
            'id' => $pqr->id,
            'estado' => 'resuelta',
            'respuesta' => 'Respuesta recuperada del trámite anterior.',
            'respondida_por' => $admin->id,
        ]);
    }

    public function test_registering_a_missing_response_does_not_reopen_a_closed_pqrs(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $pqr = Pqr::factory()->create([
            'estado' => 'cerrada',
            'respuesta' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('pqrs.edit', $pqr))
            ->assertOk()
            ->assertSee('Respuesta faltante en un registro previo')
            ->assertSee('Esta acción no reabre la PQRS.');

        $this->actingAs($admin)
            ->post(route('pqrs.respond', $pqr), ['respuesta' => 'Respuesta faltante del expediente cerrado.'])
            ->assertRedirect(route('pqrs.edit', $pqr));

        $this->assertSame('cerrada', $pqr->fresh()->estado);
        $this->assertSame('Respuesta faltante del expediente cerrado.', $pqr->fresh()->respuesta);
    }
}
