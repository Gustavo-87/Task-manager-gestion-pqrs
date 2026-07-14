<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Gestión de PQR</h2>
            <a href="{{ route('pqrs.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Nueva PQR</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <form method="GET" action="{{ route('pqrs.index') }}" class="grid gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-4">
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por asunto..." class="rounded-md border-gray-300 sm:col-span-2">
                <select name="estado" class="rounded-md border-gray-300">
                    <option value="">Todos los estados</option>
                    @foreach (['radicada' => 'Radicada', 'en_revision' => 'En revisión', 'respondida' => 'Respondida', 'cerrada' => 'Cerrada'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Filtrar</button>
            </form>

            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                            <tr><th class="px-4 py-3">Asunto</th><th class="px-4 py-3">Tipo</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Radicación</th><th class="px-4 py-3">Usuario</th><th class="px-4 py-3">Acciones</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($pqrs as $pqr)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $pqr->asunto }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $pqr->tipoPqr?->nombre ?? 'Sin tipo' }}</td>
                                    <td class="px-4 py-3"><span class="rounded-full bg-indigo-50 px-2 py-1 text-xs text-indigo-700">{{ str_replace('_', ' ', $pqr->estado) }}</span></td>
                                    <td class="px-4 py-3 text-gray-600">{{ $pqr->fecha_radicacion }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $pqr->user?->name ?? 'Sin usuario' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-3">
                                            <a href="{{ route('pqrs.edit', $pqr) }}" class="font-medium text-indigo-600 hover:text-indigo-800">Editar</a>
                                            @can('delete', $pqr)
                                                <form action="{{ route('pqrs.destroy', $pqr) }}" method="POST" onsubmit="return confirm('¿Eliminar esta PQR?')">
                                                    @csrf @method('DELETE')
                                                    <button class="font-medium text-red-600 hover:text-red-800">Eliminar</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">No hay PQR registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
