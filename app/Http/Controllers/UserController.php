<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Services\AuditLogger;

class UserController extends Controller
{
    public function index(Request $request): View
    {
       $stats = (object) [
    'total' => User::count(),
    'admins' => User::where('rol', 'admin')->count(),
    'residentes' => User::where('rol', 'residente')->count(),
    'inactivos' => User::where('activo', false)->count(),
        ];

        $users = User::query()
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $term = '%'.$request->string('buscar')->trim().'%';
                $query->where(fn ($query) => $query->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->when($request->filled('rol'), fn ($query) => $query->where('rol', $request->string('rol')))
            ->when($request->filled('estado'), fn ($query) => $query->where('activo', $request->string('estado') === 'activo'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'stats'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $user = User::create($data);
        AuditLogger::log($request, 'Usuarios', 'crear', "Creó al usuario {$user->email}", $user, null, $user->getAttributes());

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate($this->rules($user));

        if ($request->user()->is($user)) {
            if (! $request->boolean('activo') || $data['rol'] !== 'admin') {
                return back()->withErrors(['activo' => 'No puedes desactivar tu cuenta ni quitarte el rol de administrador.'])->withInput();
            }
        }

        $passwordChanged = filled($data['password'] ?? null);
        if (! $passwordChanged) {
            unset($data['password']);
        }

        $data['activo'] = $request->boolean('activo');
        $oldValues = $user->only(['name', 'email', 'rol', 'activo']);
        $user->update($data);
        $newValues = $user->only(['name', 'email', 'rol', 'activo']);
        $changes = collect($newValues)->filter(fn ($value, $key) => $oldValues[$key] !== $value)->all();

        if ($changes !== []) {
            $oldChanges = collect($changes)->mapWithKeys(fn ($value, $key) => [$key => $oldValues[$key]])->all();
            AuditLogger::log($request, 'Usuarios', 'actualizar', "Actualizó al usuario {$user->email}", $user, $oldChanges, $changes);
        }
        if ($oldValues['rol'] !== $newValues['rol']) {
            AuditLogger::log($request, 'Usuarios', 'cambiar_rol', "Cambió el rol de {$user->email}", $user, ['rol' => $oldValues['rol']], ['rol' => $newValues['rol']]);
        }
        if ($oldValues['activo'] !== $newValues['activo']) {
            $action = $newValues['activo'] ? 'activar' : 'desactivar';
            AuditLogger::log($request, 'Usuarios', $action, ucfirst($action)." la cuenta {$user->email}", $user, ['activo' => $oldValues['activo']], ['activo' => $newValues['activo']]);
        }
        if ($passwordChanged) {
            AuditLogger::log($request, 'Usuarios', 'restablecer_contrasena', "Restableció la contraseña de {$user->email}", $user);
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['usuario' => 'No puedes eliminar tu propia cuenta de administrador.']);
        }

        $snapshot = $user->only(['name', 'email', 'rol', 'activo']);
        AuditLogger::log($request, 'Usuarios', 'eliminar', "Eliminó al usuario {$user->email}", $user, $snapshot);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($user)],
            'rol' => ['required', Rule::in(['admin', 'residente'])],
            'activo' => ['sometimes', 'boolean'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', 'min:8'],
        ];
    }
}
