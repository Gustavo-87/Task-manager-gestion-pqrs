<?php

namespace Tests\Feature;

use App\Models\NotificationDelivery;
use App\Models\Pqr;
use App\Models\TipoPqr;
use App\Models\User;
use App\Notifications\PqrDeadlineReminder;
use App\Notifications\PqrStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PqrNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_resident_is_emailed_when_admin_changes_pqr_status(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['rol' => 'admin']);
        $resident = User::factory()->create(['rol' => 'residente']);
        $tipo = TipoPqr::factory()->create();
        $pqr = Pqr::factory()->create([
            'user_id' => $resident->id,
            'tipo_pqr_id' => $tipo->id,
            'estado' => 'radicada',
        ]);

        $this->actingAs($admin)->put(route('pqrs.update', $pqr), [
            'asunto' => $pqr->asunto,
            'descripcion' => $pqr->descripcion,
            'fecha_radicacion' => $pqr->fecha_radicacion->toDateString(),
            'tipo_pqr_id' => $tipo->id,
            'estado' => 'en_revision',
        ])->assertRedirect(route('pqrs.index'));

        Notification::assertSentTo($resident, PqrStatusChanged::class, fn ($notification) =>
            $notification->previousStatus === 'radicada' && $notification->newStatus === 'en_revision'
        );
        $this->assertDatabaseHas('audits', [
            'module' => 'Notificaciones',
            'action' => 'enviar_correo',
            'auditable_id' => $pqr->id,
        ]);
    }

    public function test_deadline_command_notifies_active_admins_one_day_before_only_once(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-07-14 08:00:00');
        $admin = User::factory()->create(['rol' => 'admin', 'activo' => true]);
        $inactiveAdmin = User::factory()->create(['rol' => 'admin', 'activo' => false]);
        $pqr = Pqr::factory()->create([
            'estado' => 'en_revision',
            'fecha_limite_respuesta' => '2026-07-15',
        ]);

        $this->artisan('pqrs:notify-deadlines')->assertSuccessful();
        $this->artisan('pqrs:notify-deadlines')->assertSuccessful();

        Notification::assertSentToTimes($admin, PqrDeadlineReminder::class, 1);
        Notification::assertNotSentTo($inactiveAdmin, PqrDeadlineReminder::class);
        $this->assertSame(1, NotificationDelivery::where('pqr_id', $pqr->id)->count());
    }

    public function test_deadline_command_notifies_on_due_date_and_ignores_completed_pqrs(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-07-14 08:00:00');
        $admin = User::factory()->create(['rol' => 'admin', 'activo' => true]);
        $due = Pqr::factory()->create(['estado' => 'radicada', 'fecha_limite_respuesta' => '2026-07-14']);
        Pqr::factory()->create(['estado' => 'respondida', 'fecha_limite_respuesta' => '2026-07-14']);
        Pqr::factory()->create(['estado' => 'cerrada', 'fecha_limite_respuesta' => '2026-07-15']);

        $this->artisan('pqrs:notify-deadlines')->assertSuccessful();

        Notification::assertSentTo($admin, PqrDeadlineReminder::class, fn ($notification) =>
            $notification->pqr->is($due) && $notification->daysRemaining === 0
        );
        Notification::assertSentToTimes($admin, PqrDeadlineReminder::class, 1);
    }
}
