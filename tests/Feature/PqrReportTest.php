<?php

namespace Tests\Feature;

use App\Mail\PqrReportMail;
use App\Models\Pqr;
use App\Models\TipoPqr;
use App\Models\User;
use App\Services\PqrReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PqrReportTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_only_admin_can_access_reports(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $resident = User::factory()->create(['rol' => 'residente']);

        $this->actingAs($admin)->get(route('reports.index'))->assertOk()->assertSee('Generar reporte');
        $this->actingAs($resident)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($resident)->post(route('reports.download'), [
            'date_from' => '2026-07-01', 'date_to' => '2026-07-31',
        ])->assertForbidden();
    }

    public function test_admin_can_download_pdf_and_actions_are_audited(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        Pqr::factory()->create(['fecha_radicacion' => '2026-07-14']);

        $response = $this->actingAs($admin)->post(route('reports.download'), [
            'date_from' => '2026-07-01',
            'date_to' => '2026-07-31',
        ]);

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertDatabaseHas('audits', ['module' => 'Reportes', 'action' => 'generar', 'user_id' => $admin->id]);
        $this->assertDatabaseHas('audits', ['module' => 'Reportes', 'action' => 'descargar', 'user_id' => $admin->id]);
    }

    public function test_admin_can_email_pdf_to_their_own_address(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post(route('reports.email'), [
            'date_from' => '2026-07-01',
            'date_to' => '2026-07-31',
        ])->assertRedirect()->assertSessionHas('success');

        Mail::assertSent(PqrReportMail::class, fn ($mail) =>
            $mail->hasTo($admin->email) && count($mail->attachments()) === 1
        );
        $this->assertDatabaseHas('audits', ['module' => 'Reportes', 'action' => 'enviar_correo', 'user_id' => $admin->id]);
    }

    public function test_report_validates_date_range(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post(route('reports.download'), [
            'date_from' => '2026-07-31',
            'date_to' => '2026-07-01',
        ])->assertSessionHasErrors('date_to');
    }

    public function test_report_calculates_status_category_and_deadline_summaries(): void
    {
        Carbon::setTestNow('2026-07-14 10:00:00');
        $category = TipoPqr::factory()->create(['nombre' => 'Petición']);
        Pqr::factory()->create(['tipo_pqr_id' => $category->id, 'estado' => 'radicada', 'fecha_radicacion' => '2026-07-01', 'fecha_limite_respuesta' => '2026-07-13']);
        Pqr::factory()->create(['tipo_pqr_id' => $category->id, 'estado' => 'en_revision', 'fecha_radicacion' => '2026-07-02', 'fecha_limite_respuesta' => '2026-07-15']);
        Pqr::factory()->create(['estado' => 'cerrada', 'fecha_radicacion' => '2026-06-30', 'fecha_limite_respuesta' => '2026-07-01']);

        $data = app(PqrReportService::class)->data('2026-07-01', '2026-07-31');

        $this->assertCount(2, $data['pqrs']);
        $this->assertSame(1, $data['byStatus']['radicada']);
        $this->assertSame(2, $data['byCategory']['Petición']);
        $this->assertCount(1, $data['overdue']);
        $this->assertCount(1, $data['upcoming']);
    }
}
