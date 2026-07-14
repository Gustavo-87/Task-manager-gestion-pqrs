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
            ->selectRaw("SUM(CASE WHEN estado = 'respondida' THEN 1 ELSE 0 END) as respondidas")
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
        AuditLogger::log($request, 'PQR', 'crear', "Creó la PQR #{$pqr->id}: {$pqr->asunto}", $pqr, null, $pqr->getAttributes());

        return redirect()->route('pqrs.index')->with('success', 'PQR creada correctamente.');
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
            'estado' => ['required', Rule::in(['radicada', 'en_revision', 'respondida', 'cerrada'])],
        ]);
        $original = ['estado' => $pqr->estado];

        $pqr->update($data);

        $changes = collect($pqr->getChanges())->except('updated_at')->all();
        if ($changes !== []) {
            $oldValues = collect($changes)->mapWithKeys(fn ($value, $field) => [$field => $original[$field] ?? null])->all();
            AuditLogger::log($request, 'PQR', 'actualizar', "Actualizó la PQR #{$pqr->id}: {$pqr->asunto}", $pqr, $oldValues, $changes);

            if (array_key_exists('estado', $changes)) {
                AuditLogger::log($request, 'PQR', 'cambiar_estado', "Cambió el estado de la PQR #{$pqr->id}", $pqr, ['estado' => $oldValues['estado']], ['estado' => $changes['estado']]);
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

    public function respond(Request $request, Pqr $pqr, PqrNotificationService $notifications)
    {
        Gate::authorize('respond', $pqr);

        $validated = $request->validate([
            'respuesta' => ['required', 'string', 'max:10000'],
        ]);
        $previousStatus = $pqr->estado;
        $newStatus = $previousStatus === 'cerrada' ? 'cerrada' : 'respondida';

        DB::transaction(function () use ($request, $pqr, $validated, $previousStatus, $newStatus): void {
            $pqr->update([
                'respuesta' => $validated['respuesta'],
                'respondida_en' => now(),
                'respondida_por' => $request->user()->id,
                'estado' => $newStatus,
            ]);

            foreach (['respuesta', 'respondida_en', 'respondida_por', 'estado'] as $field) {
                PqrHistory::create([
                    'pqr_id' => $pqr->id,
                    'campo' => $field,
                    'valor_anterior' => $field === 'estado' ? $previousStatus : null,
                    'valor_nuevo' => $pqr->{$field},
                    'user_id' => $request->user()->id,
                ]);
            }

            AuditLogger::log(
                $request,
                'PQR',
                'responder',
                "Respondió la PQR #{$pqr->id}: {$pqr->asunto}",
                $pqr,
                ['estado' => $previousStatus],
                ['estado' => $newStatus, 'respuesta' => $validated['respuesta']],
            );
        });

        $notifications->sendStatusChanged($request, $pqr->load('user'), $previousStatus, $newStatus);

        return redirect()->route('pqrs.edit', $pqr)->with('success', 'La PQR fue respondida correctamente.');
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
                "Editó la respuesta de la PQR #{$pqr->id}: {$pqr->asunto}",
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
        AuditLogger::log($request, 'PQR', 'eliminar', "Eliminó la PQR #{$pqr->id}: {$pqr->asunto}", $pqr, $snapshot);
        $pqr->delete();

        return redirect()->route('pqrs.index')->with('success', 'PQR eliminada correctamente.');
    }
}
