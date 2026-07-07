<?php

namespace App\Http\Controllers;

use App\Models\Pqr;
use App\Models\TipoPqr;
use Illuminate\Http\Request;

class PqrController extends Controller
{
    public function index(Request $request)
    {
        $query = Pqr::with(['user', 'tipoPqr']);

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
        $tipos = TipoPqr::orderBy('nombre')->get();

        return view('pqrs.create', compact('tipos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asunto' => 'required|string|max:150',
            'descripcion' => 'required|string',
            'fecha_radicacion' => 'required|date',
            'fecha_limite_respuesta' => 'nullable|date',
            'tipo_pqr_id' => 'required|exists:tipo_pqrs,id',
        ]);

        $data = $request->all();
        $data['user_id'] = 1;

        Pqr::create($data);

        return redirect()->route('pqrs.index')->with('success', 'PQR creada correctamente.');
    }

    public function edit(Pqr $pqr)
    {
        $tipos = TipoPqr::orderBy('nombre')->get();

        return view('pqrs.edit', compact('pqr', 'tipos'));
    }

    public function update(Request $request, Pqr $pqr)
    {
        $request->validate([
            'asunto' => 'required|string|max:150',
            'descripcion' => 'required|string',
            'fecha_radicacion' => 'required|date',
            'fecha_limite_respuesta' => 'nullable|date',
            'estado' => 'required|in:radicada,en_revision,respondida,cerrada',
            'tipo_pqr_id' => 'required|exists:tipo_pqrs,id',
        ]);

        $pqr->update($request->all());

        return redirect()->route('pqrs.index')->with('success', 'PQR actualizada correctamente.');
    }

    public function destroy(Pqr $pqr)
    {
        $pqr->delete();

        return redirect()->route('pqrs.index')->with('success', 'PQR eliminada correctamente.');
    }
}
