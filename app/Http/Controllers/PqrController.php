<?php

namespace App\Http\Controllers;

use App\Models\Pqr;
use App\Models\PqrHistory;
use App\Models\TipoPqr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\AuditLogger;
use Illuminate\Support\Carbon;

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

        $pqrs = $query->orderBy('created_at', 'desc')->get();

        return view('pqrs.index', compact('pqrs'));
    }

    public function create()
    {
        Gate::authorize('create', Pqr::class);

        $tipos = TipoPqr::orderBy('nombre')->get();

        return view('pqrs.create', compact('tipos'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Pqr::class);

        $request->validate([
            'asunto' => 'required|string|max:150',
            'descripcion' => 'required|string',
            'fecha_radicacion' => 'required|date',
            'tipo_pqr_id' => 'required|exists:tipo_pqrs,id',
        ]);

        $data = $request->only([
            'asunto', 'descripcion', 'fecha_radicacion', 'tipo_pqr_id',
        ]);
        $data['fecha_limite_respuesta'] = Carbon::parse($data['fecha_radicacion'])->addDays(15)->toDateString();
        $data['user_id'] = $request->user()->id;

        $pqr = Pqr::create($data);
        AuditLogger::log($request, 'PQR', 'crear', "Creó la PQR #{$pqr->id}: {$pqr->asunto}", $pqr, null, $pqr->getAttributes());

        return redirect()->route('pqrs.index')->with('success', 'PQR creada correctamente.');
    }

    public function edit(Pqr $pqr)
    {
        Gate::authorize('update', $pqr);

        $tipos = TipoPqr::orderBy('nombre')->get();

        return view('pqrs.edit', compact('pqr', 'tipos'));
    }

    public function update(Request $request, Pqr $pqr)
    {
        Gate::authorize('update', $pqr);

        $rules = [
            'asunto' => 'required|string|max:150',
            'descripcion' => 'required|string',
            'fecha_radicacion' => 'required|date',
            'tipo_pqr_id' => 'required|exists:tipo_pqrs,id',
        ];

        if ($request->user()->rol === 'admin') {
            $rules['estado'] = 'required|in:radicada,en_revision,respondida,cerrada';
        }

        $request->validate($rules);

        $fields = [
            'asunto', 'descripcion', 'fecha_radicacion', 'tipo_pqr_id',
        ];

        if ($request->user()->rol === 'admin') {
            $fields[] = 'estado';
        }

        $data = $request->only($fields);
        $data['fecha_limite_respuesta'] = Carbon::parse($data['fecha_radicacion'])->addDays(15)->toDateString();
        $original = $pqr->only(array_keys($data));

        $pqr->update($data);

        $changes = collect($pqr->getChanges())->except('updated_at')->all();
        if ($changes !== []) {
            $oldValues = collect($changes)->mapWithKeys(fn ($value, $field) => [$field => $original[$field] ?? null])->all();
            AuditLogger::log($request, 'PQR', 'actualizar', "Actualizó la PQR #{$pqr->id}: {$pqr->asunto}", $pqr, $oldValues, $changes);

            if (array_key_exists('estado', $changes)) {
                AuditLogger::log($request, 'PQR', 'cambiar_estado', "Cambió el estado de la PQR #{$pqr->id}", $pqr, ['estado' => $oldValues['estado']], ['estado' => $changes['estado']]);
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

        return redirect()->route('pqrs.index')->with('success', 'PQR actualizada correctamente.');
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
