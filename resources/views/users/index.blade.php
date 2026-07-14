<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Gestión de usuarios</h2>
            <a href="{{ route('users.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Nuevo usuario</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            <form method="GET" action="{{ route('users.index') }}" class="grid gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-4">
                <input name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o correo..." class="rounded-md border-gray-300 sm:col-span-2">
                <select name="rol" class="rounded-md border-gray-300">
                    <option value="">Todos los roles</option>
                    <option value="admin" @selected(request('rol') === 'admin')>Administrador</option>
                    <option value="residente" @selected(request('rol') === 'residente')>Residente</option>
                </select>
                <button class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Filtrar</button>
            </form>

            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                            <tr><th class="px-4 py-3">Usuario</th><th class="px-4 py-3">Rol</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Registro</th><th class="px-4 py-3">Acciones</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-4 py-3"><div class="font-medium text-gray-900">{{ $user->name }}</div><div class="text-gray-500">{{ $user->email }}</div></td>
                                    <td class="px-4 py-3 text-gray-600">{{ $user->rol === 'admin' ? 'Administrador' : 'Residente' }}</td>
                                    <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">{{ $user->activo ? 'Activo' : 'Inactivo' }}</span></td>
                                    <td class="px-4 py-3 text-gray-600">{{ $user->created_at->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3"><div class="flex gap-3">
                                        <a href="{{ route('users.edit', $user) }}" class="font-medium text-indigo-600 hover:text-indigo-800">Editar</a>
                                        @unless (auth()->user()->is($user))
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('¿Eliminar este usuario y sus PQR asociadas?')">
                                                @csrf @method('DELETE')
                                                <button class="font-medium text-red-600 hover:text-red-800">Eliminar</button>
                                            </form>
                                        @endunless
                                    </div></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">No hay usuarios registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($users->hasPages())<div class="border-t border-gray-200 px-4 py-3">{{ $users->links() }}</div>@endif
            </div>
        </div>
    </div>
</x-app-layout>
