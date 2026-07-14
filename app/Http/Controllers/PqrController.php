<?php

namespace App\Http\Controllers;

use App\Models\Pqr;
use App\Models\PqrHistory;
use App\Models\TipoPqr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
            'fecha_limite_respuesta' => 'nullable|date',
            'tipo_pqr_id' => 'required|exists:tipo_pqrs,id',
        ]);

        $data = $request->only([
            'asunto', 'descripcion', 'fecha_radicacion',
            'fecha_limite_respuesta', 'tipo_pqr_id',
        ]);
        $data['user_id'] = $request->user()->id;

        Pqr::create($data);

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
            'fecha_limite_respuesta' => 'nullable|date',
            'tipo_pqr_id' => 'required|exists:tipo_pqrs,id',
        ];

        if ($request->user()->rol === 'admin') {
            $rules['estado'] = 'required|in:radicada,en_revision,respondida,cerrada';
        }

        $request->validate($rules);

        $fields = [
            'asunto', 'descripcion', 'fecha_radicacion',
            'fecha_limite_respuesta', 'tipo_pqr_id',
        ];

        if ($request->user()->rol === 'admin') {
            $fields[] = 'estado';
        }

        $data = $request->only($fields);
        $original = $pqr->only(array_keys($data));

        $pqr->update($data);

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

    public function destroy(Pqr $pqr)
    {
        Gate::authorize('delete', $pqr);

        $pqr->delete();

        return redirect()->route('pqrs.index')->with('success', 'PQR eliminada correctamente.');
    }
}
