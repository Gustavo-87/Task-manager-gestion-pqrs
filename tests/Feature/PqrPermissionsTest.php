<?php

namespace Tests\Feature;

use App\Models\Pqr;
use App\Models\TipoPqr;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PqrPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_resident_can_only_view_their_own_pqrs(): void
    {
        $resident = User::factory()->create(['role' => 'residente']);
        $other = User::factory()->create(['role' => 'residente']);
        $ownPqr = $this->makePqr($resident);
        $otherPqr = $this->makePqr($other);

        $this->actingAs($resident)->get(route('pqrs.show', $ownPqr))->assertOk();
        $this->actingAs($resident)->get(route('pqrs.show', $otherPqr))->assertForbidden();
    }

    public function test_resident_cannot_edit_a_submitted_pqr(): void
    {
        $resident = User::factory()->create(['role' => 'residente']);
        $pqr = $this->makePqr($resident);

        $this->actingAs($resident)->get(route('pqrs.edit', $pqr))->assertForbidden();
        $this->actingAs($resident)->delete(route('pqrs.destroy', $pqr))->assertForbidden();
    }

    public function test_manager_can_edit_any_pqr(): void
    {
        $manager = User::factory()->create(['role' => 'gestor']);
        $pqr = $this->makePqr(User::factory()->create(['role' => 'residente']));

        $this->actingAs($manager)->get(route('pqrs.edit', $pqr))->assertOk();
    }

    public function test_resident_can_submit_a_pqr_with_a_private_attachment(): void
    {
        Storage::fake('local');
        $resident = User::factory()->create(['role' => 'residente']);
        $tipo = TipoPqr::create(['nombre' => 'Petición', 'descripcion' => 'Petición']);

        $this->actingAs($resident)->post(route('pqrs.store'), [
            'asunto' => 'Solicitud con soporte',
            'descripcion' => 'Descripción completa de la solicitud.',
            'fecha_radicacion' => now()->format('Y-m-d'),
            'tipo_pqr_id' => $tipo->id,
            'adjuntos' => [UploadedFile::fake()->image('evidencia.jpg')],
        ])->assertRedirect();

        $pqr = Pqr::firstOrFail();
        $attachment = $pqr->attachments()->firstOrFail();
        Storage::disk('local')->assertExists($attachment->path);
        $this->assertSame($resident->id, $pqr->user_id);
    }

    private function makePqr(User $user): Pqr
    {
        $tipo = TipoPqr::firstOrCreate(['nombre' => 'Queja'], ['descripcion' => 'Queja']);

        return Pqr::factory()->create(['user_id' => $user->id, 'tipo_pqr_id' => $tipo->id]);
    }
}
