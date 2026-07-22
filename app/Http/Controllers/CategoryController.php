<?php

namespace App\Http\Controllers;

use App\Models\TipoPqr;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function create(): View
    {
        return view('configuration.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $category = TipoPqr::create($request->validate($this->rules()));
        AuditLogger::log($request, 'Categorías', 'crear', "Creó la categoría {$category->nombre}.", $category, null, $category->getAttributes());

        return redirect()->route('configuration.index')->with('success', 'Categoría creada correctamente.');
    }

    public function edit(TipoPqr $category): View
    {
        return view('configuration.categories.edit', compact('category'));
    }

    public function update(Request $request, TipoPqr $category): RedirectResponse
    {
        $data = $request->validate($this->rules($category));
        $data['activo'] = $request->boolean('activo');
        $oldValues = $category->only(['nombre', 'descripcion', 'activo']);
        $category->update($data);
        AuditLogger::log($request, 'Categorías', 'actualizar', "Actualizó la categoría {$category->nombre}.", $category, $oldValues, $category->only(['nombre', 'descripcion', 'activo']));

        return redirect()->route('configuration.index')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Request $request, TipoPqr $category): RedirectResponse
    {
        if ($category->pqrs()->exists()) {
            return back()->withErrors(['category' => 'No puedes eliminar una categoría que tenga PQRS asociadas. Puedes desactivarla.']);
        }

        AuditLogger::log($request, 'Categorías', 'eliminar', "Eliminó la categoría {$category->nombre}.", $category, $category->getAttributes());
        $category->delete();

        return redirect()->route('configuration.index')->with('success', 'Categoría eliminada correctamente.');
    }

    private function rules(?TipoPqr $category = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100', Rule::unique('tipo_pqrs', 'nombre')->ignore($category)],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}
