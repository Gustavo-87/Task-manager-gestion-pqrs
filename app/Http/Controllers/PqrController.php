<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Pqr;
use App\Models\PqrHistory;
use App\Models\TipoPqr;
use App\Services\AuditLogger;
use App\Services\PqrNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PqrController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Pqr::class);

        $query = Pqr::with(['user', 'tipoPqr']);

        if ($request->user()->rol !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $summaryQuery = Pqr::query();
        if ($request->user()->rol !== 'admin') {
            $summaryQuery->where('user_id', $request->user()->id);
        }

        $stats = (clone $summaryQuery)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN estado = 'radicada' THEN 1 ELSE 0 END) as radicadas")
            ->selectRaw("SUM(CASE WHEN estado = 'en_revision' THEN 1 ELSE 0 END) as en_revision")
            ->selectRaw("SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso")
            ->selectRaw("SUM(CASE WHEN estado = 'en_espera' THEN 1 ELSE 0 END) as en_espera")
            ->selectRaw("SUM(CASE WHEN estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas")
            ->selectRaw("SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as resueltas")
            ->selectRaw("SUM(CASE WHEN estado = 'cerrada' THEN 1 ELSE 0 END) as cerradas")
            ->first();

        $pqrs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('pqrs.index', compact('pqrs', 'stats'));
    }

    public function create()
    {
        Gate::authorize('create', Pqr::class);

        $tipos = TipoPqr::where('activo', true)->orderBy('nombre')->get();

        return view('pqrs.create', compact('tipos'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Pqr::class);

        $request->validate([
            'asunto' => 'required|string|max:150',
            'descripcion' => 'required|string',
            'fecha_radicacion' => 'required|date',
            'tipo_pqr_id' => ['required', Rule::exists('tipo_pqrs', 'id')->where('activo', true)],
        ]);

        $data = $request->only([
            'asunto', 'descripcion', 'fecha_radicacion', 'tipo_pqr_id',
        ]);
        $data['fecha_limite_respuesta'] = Carbon::parse($data['fecha_radicacion'])->addDays(AppSetting::current()->response_days)->toDateString();
        $data['user_id'] = $request->user()->id;

        $pqr = Pqr::create($data);
        AuditLogger::log($request, 'PQR', 'crear', "Creó la PQRS #{$pqr->id}: {$pqr->asunto}", $pqr, null, $pqr->getAttributes());

        return redirect()->route('pqrs.index')->with('success', 'PQRS creada correctamente.');
    }

    public function edit(Pqr $pqr)
    {
        Gate::authorize('view', $pqr);

        $tipos = TipoPqr::where('activo', true)
            ->orWhere('id', $pqr->tipo_pqr_id)
            ->orderBy('nombre')
            ->get();

        return view('pqrs.edit', compact('pqr', 'tipos'));
    }

    public function update(Request $request, Pqr $pqr, PqrNotificationService $notifications)
    {
        Gate::authorize('update', $pqr);

        $data = $request->validate([
            'estado' => ['required', Rule::in(array_keys(Pqr::statuses()))],
        ]);

        if (! Pqr::canTransitionTo($pqr->estado, $data['estado'])) {
            throw ValidationException::withMessages([
                'estado' => 'No es posible devolver el estado de una PQRS.',
            ]);
        }

        $original = ['estado' => $pqr->estado];

        $pqr->update($data);

        $changes = collect($pqr->getChanges())->except('updated_at')->all();
        if ($changes !== []) {
            $oldValues = collect($changes)->mapWithKeys(fn ($value, $field) => [$field => $original[$field] ?? null])->all();
            AuditLogger::log($request, 'PQR', 'actualizar', "Actualizó la PQRS #{$pqr->id}: {$pqr->asunto}", $pqr, $oldValues, $changes);

            if (array_key_exists('estado', $changes)) {
                AuditLogger::log($request, 'PQR', 'cambiar_estado', "Cambió el estado de la PQRS #{$pqr->id}", $pqr, ['estado' => $oldValues['estado']], ['estado' => $changes['estado']]);
                $notifications->sendStatusChanged($request, $pqr->load('user'), $oldValues['estado'], $changes['estado']);
            }
        }

        foreach ($pqr->getChanges() as $field => $newValue) {
            if ($field === 'updated_at') {
                continue;
            }

            PqrHistory::create([
                'pqr_id' => $pqr->id,
                'campo' => $field,
                'valor_anterior' => $original[$field] ?? null,
                'valor_nuevo' => $newValue,
                'user_id' => $request->user()->id,
            ]);
        }

        return redirect()->route('pqrs.edit', $pqr)->with('success', 'Estado actualizado correctamente.');
    }

    public function transitionWorkflow(Request $request, Pqr $pqr, PqrNotificationService $notifications)
    {
        Gate::authorize('manageWorkflow', $pqr);

        $validated = $request->validate([
            'action' => ['required', Rule::in(array_keys(Pqr::workflowTransitions()))],
        ]);

        $transition = Pqr::transitionForAction($validated['action']);

        if ($transition === null || ! Pqr::canApplyWorkflowAction($pqr->estado, $validated['action'])) {
            throw ValidationException::withMessages([
                'action' => 'La acción solicitada no está disponible para el estado actual de la PQRS.',
            ]);
        }

        $previousStatus = $pqr->estado;
        $nextStatus = $transition['to'];

        DB::transaction(function () use ($request, $pqr, $previousStatus, $nextStatus, $validated): void {
            $pqr->update(['estado' => $nextStatus]);

            PqrHistory::create([
                'pqr_id' => $pqr->id,
                'campo' => 'estado',
                'valor_anterior' => $previousStatus,
                'valor_nuevo' => $nextStatus,
                'user_id' => $request->user()->id,
            ]);

            AuditLogger::log(
                $request,
                'PQR',
                $validated['action'],
                "Cambió el estado de la PQRS #{$pqr->id}",
                $pqr,
                ['estado' => $previousStatus],
                ['estado' => $nextStatus],
            );
        });

        $notifications->sendStatusChanged($request, $pqr->load('user'), $previousStatus, $nextStatus);

        return redirect()
            ->route('pqrs.edit', $pqr)
            ->with('success', 'Estado actualizado a '.Pqr::statusLabel($nextStatus).'.');
    }

    public function respond(Request $request, Pqr $pqr, PqrNotificationService $notifications)
    {
        Gate::authorize('respond', $pqr);

        $validated = $request->validate([
            'respuesta' => ['required', 'string', 'max:10000'],
        ]);
        $previousStatus = $pqr->estado;
        $newStatus = in_array($previousStatus, Pqr::responseCompletionStatuses(), true)
            ? $previousStatus
            : 'resuelta';

        DB::transaction(function () use ($request, $pqr, $validated, $previousStatus, $newStatus): void {
            $pqr->update([
                'respuesta' => $validated['respuesta'],
                'respondida_en' => now(),
                'respondida_por' => $request->user()->id,
                'estado' => $newStatus,
            ]);

            foreach (['respuesta', 'respondida_en', 'respondida_por'] as $field) {
                PqrHistory::create([
                    'pqr_id' => $pqr->id,
                    'campo' => $field,
                    'valor_anterior' => null,
                    'valor_nuevo' => $pqr->{$field},
                    'user_id' => $request->user()->id,
                ]);
            }

            if ($previousStatus !== $newStatus) {
                PqrHistory::create([
                    'pqr_id' => $pqr->id,
                    'campo' => 'estado',
                    'valor_anterior' => $previousStatus,
                    'valor_nuevo' => $newStatus,
                    'user_id' => $request->user()->id,
                ]);
            }

            AuditLogger::log(
                $request,
                'PQR',
                $previousStatus === $newStatus ? 'completar_respuesta' : 'resolver',
                ($previousStatus === $newStatus ? 'Completó la respuesta de la PQRS' : 'Resolvió la PQRS')." #{$pqr->id}: {$pqr->asunto}",
                $pqr,
                ['estado' => $previousStatus],
                ['estado' => $newStatus, 'respuesta' => $validated['respuesta']],
            );
        });

        if ($previousStatus !== $newStatus) {
            $notifications->sendStatusChanged($request, $pqr->load('user'), $previousStatus, $newStatus);
        }

        return redirect()
            ->route('pqrs.edit', $pqr)
            ->with('success', $previousStatus === $newStatus
                ? 'La respuesta faltante fue registrada correctamente.'
                : 'La PQRS fue resuelta correctamente.');
    }

    public function updateResponse(Request $request, Pqr $pqr)
    {
        Gate::authorize('updateResponse', $pqr);

        $validated = $request->validate([
            'respuesta' => ['required', 'string', 'max:10000'],
        ]);
        $previousResponse = $pqr->respuesta;

        DB::transaction(function () use ($request, $pqr, $validated, $previousResponse): void {
            $pqr->update([
                'respuesta' => $validated['respuesta'],
                'respondida_en' => now(),
                'respondida_por' => $request->user()->id,
            ]);

            PqrHistory::create([
                'pqr_id' => $pqr->id,
                'campo' => 'respuesta',
                'valor_anterior' => $previousResponse,
                'valor_nuevo' => $validated['respuesta'],
                'user_id' => $request->user()->id,
            ]);

            AuditLogger::log(
                $request,
                'PQR',
                'editar_respuesta',
                "Editó la respuesta de la PQRS #{$pqr->id}: {$pqr->asunto}",
                $pqr,
                ['respuesta' => $previousResponse],
                ['respuesta' => $validated['respuesta']],
            );
        });

        return redirect()->route('pqrs.edit', $pqr)->with('success', 'La respuesta fue actualizada correctamente.');
    }

    public function destroy(Request $request, Pqr $pqr)
    {
        Gate::authorize('delete', $pqr);

        $snapshot = $pqr->getAttributes();
        AuditLogger::log($request, 'PQR', 'eliminar', "Eliminó la PQRS #{$pqr->id}: {$pqr->asunto}", $pqr, $snapshot);
        $pqr->delete();

        return redirect()->route('pqrs.index')->with('success', 'PQRS eliminada correctamente.');
    }
}
