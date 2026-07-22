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

    public function test_resident_is_emailed_when_admin_resolves_a_pqrs(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['rol' => 'admin']);
        $resident = User::factory()->create(['rol' => 'residente']);
        $tipo = TipoPqr::factory()->create();
        $pqr = Pqr::factory()->create([
            'user_id' => $resident->id,
            'tipo_pqr_id' => $tipo->id,
            'estado' => 'en_proceso',
        ]);

        $this->actingAs($admin)
            ->post(route('pqrs.respond', $pqr), ['respuesta' => 'Tu solicitud fue atendida por la administración.'])
            ->assertRedirect(route('pqrs.edit', $pqr));

        Notification::assertSentTo(
            $resident,
            PqrStatusChanged::class,
            fn ($notification) => $notification->previousStatus === 'en_proceso'
                && $notification->newStatus === 'resuelta'
        );
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

    public function test_deadline_command_ignores_inactive_workflow_states(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-07-14 08:00:00');

        $admin = User::factory()->create(['rol' => 'admin', 'activo' => true]);
        $due = Pqr::factory()->create(['estado' => 'radicada', 'fecha_limite_respuesta' => '2026-07-14']);
        Pqr::factory()->create(['estado' => 'resuelta', 'fecha_limite_respuesta' => '2026-07-14']);
        Pqr::factory()->create(['estado' => 'cerrada', 'fecha_limite_respuesta' => '2026-07-15']);
        Pqr::factory()->create(['estado' => 'rechazada', 'fecha_limite_respuesta' => '2026-07-15']);

        $this->artisan('pqrs:notify-deadlines')->assertSuccessful();

        Notification::assertSentTo(
            $admin,
            PqrDeadlineReminder::class,
            fn ($notification) => $notification->pqr->is($due) && $notification->daysRemaining === 0
        );
        Notification::assertSentToTimes($admin, PqrDeadlineReminder::class, 1);
    }
}
