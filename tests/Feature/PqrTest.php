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

    public function test_authenticated_user_can_create_a_pqr(): void
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
        ]);
        $this->assertSame('2026-07-29', Pqr::where('asunto', 'Solicitud de información')->firstOrFail()->fecha_limite_respuesta->toDateString());
    }

    public function test_updating_a_pqr_records_its_history(): void
    {
        $user = User::factory()->create(['rol' => 'admin']);
        $pqr = Pqr::factory()->create(['estado' => 'radicada']);

        $response = $this->actingAs($user)->put(route('pqrs.update', $pqr), [
            'asunto' => $pqr->asunto,
            'descripcion' => $pqr->descripcion,
            'fecha_radicacion' => $pqr->fecha_radicacion->format('Y-m-d'),
            'fecha_limite_respuesta' => $pqr->fecha_limite_respuesta->format('Y-m-d'),
            'estado' => 'en_revision',
            'tipo_pqr_id' => $pqr->tipo_pqr_id,
        ]);

        $response->assertRedirect(route('pqrs.index'));
        $this->assertDatabaseHas('pqr_histories', [
            'pqr_id' => $pqr->id,
            'campo' => 'estado',
            'valor_anterior' => 'radicada',
            'valor_nuevo' => 'en_revision',
            'user_id' => $user->id,
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

    public function test_resident_cannot_edit_another_users_pqr(): void
    {
        $resident = User::factory()->create(['rol' => 'residente']);
        $otherPqr = Pqr::factory()->create();

        $this->actingAs($resident)
            ->get(route('pqrs.edit', $otherPqr))
            ->assertForbidden();
    }

    public function test_resident_cannot_change_status_or_delete_a_pqr(): void
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
        ])->assertRedirect(route('pqrs.index'));

        $this->assertDatabaseHas('pqrs', [
            'id' => $pqr->id,
            'asunto' => 'Asunto actualizado',
            'estado' => 'radicada',
        ]);

        $this->actingAs($resident)
            ->delete(route('pqrs.destroy', $pqr))
            ->assertForbidden();

        $this->assertDatabaseHas('pqrs', ['id' => $pqr->id]);
    }

    public function test_admin_can_see_and_delete_any_pqr(): void
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

    public function test_admin_can_respond_to_a_pqr(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $pqr = Pqr::factory()->create(['estado' => 'en_revision']);

        $this->actingAs($admin)
            ->post(route('pqrs.respond', $pqr), ['respuesta' => 'La reparación quedó programada para mañana.'])
            ->assertRedirect(route('pqrs.edit', $pqr));

        $this->assertDatabaseHas('pqrs', [
            'id' => $pqr->id,
            'respuesta' => 'La reparación quedó programada para mañana.',
            'estado' => 'respondida',
            'respondida_por' => $admin->id,
        ]);
        $this->assertDatabaseHas('pqr_histories', [
            'pqr_id' => $pqr->id,
            'campo' => 'respuesta',
            'user_id' => $admin->id,
        ]);
    }

    public function test_resident_cannot_respond_to_a_pqr(): void
    {
        $resident = User::factory()->create(['rol' => 'residente']);
        $pqr = Pqr::factory()->create(['user_id' => $resident->id, 'estado' => 'en_revision']);

        $this->actingAs($resident)
            ->post(route('pqrs.respond', $pqr), ['respuesta' => 'Respuesta no autorizada.'])
            ->assertForbidden();

        $this->assertNull($pqr->fresh()->respuesta);
    }

    public function test_pqr_cannot_be_marked_as_answered_without_a_response(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $pqr = Pqr::factory()->create(['estado' => 'en_revision']);

        $this->actingAs($admin)->put(route('pqrs.update', $pqr), [
            'asunto' => $pqr->asunto,
            'descripcion' => $pqr->descripcion,
            'fecha_radicacion' => $pqr->fecha_radicacion->format('Y-m-d'),
            'estado' => 'respondida',
            'tipo_pqr_id' => $pqr->tipo_pqr_id,
        ])->assertSessionHasErrors('estado');

        $this->assertSame('en_revision', $pqr->fresh()->estado);
    }

    public function test_admin_can_edit_an_existing_response_and_preserve_its_history(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $pqr = Pqr::factory()->create([
            'estado' => 'respondida',
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
            'estado' => 'respondida',
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

    public function test_resident_cannot_edit_an_existing_response(): void
    {
        $resident = User::factory()->create(['rol' => 'residente']);
        $pqr = Pqr::factory()->create([
            'user_id' => $resident->id,
            'estado' => 'respondida',
            'respuesta' => 'Respuesta inicial.',
        ]);

        $this->actingAs($resident)
            ->patch(route('pqrs.response.update', $pqr), ['respuesta' => 'Cambio no autorizado.'])
            ->assertForbidden();

        $this->assertSame('Respuesta inicial.', $pqr->fresh()->respuesta);
    }
}
