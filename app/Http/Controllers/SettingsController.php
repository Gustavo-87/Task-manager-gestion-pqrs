<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        abort_unless(in_array($request->user()->role, ['admin','gestor'], true), 403);

        return view('settings.edit', ['settings' => SiteSetting::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['admin','gestor'], true), 403);
        $data = $request->validate([
            'nombre_conjunto' => ['required', 'string', 'max:150'],
            'nit' => ['nullable', 'string', 'max:40'],
            'representante_legal' => ['nullable', 'string', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:180'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'color_principal' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'dias_respuesta' => ['required', 'integer', 'between:1,120'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);
        $settings = SiteSetting::first() ?? new SiteSetting();
        unset($data['logo']);
        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('branding', 'public');
        }
        $settings->fill($data)->save();

        return back()->with('success', 'La configuración del conjunto fue actualizada.');
    }
}
