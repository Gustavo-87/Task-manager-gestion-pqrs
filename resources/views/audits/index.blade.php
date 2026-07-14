<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">Auditoría del sistema</h2></x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('audits.index') }}" class="grid gap-3 rounded-lg bg-white p-4 shadow md:grid-cols-4">
                <input name="buscar" value="{{ request('buscar') }}" placeholder="Buscar en la descripción..." class="rounded-md border-gray-300 md:col-span-2">
                <select name="user_id" class="rounded-md border-gray-300">
                    <option value="">Todos los usuarios</option>
                    @foreach ($users as $user)<option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>@endforeach
                </select>
                <select name="module" class="rounded-md border-gray-300">
                    <option value="">Todos los módulos</option>
                    @foreach ($modules as $module)<option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>@endforeach
                </select>
                <select name="action" class="rounded-md border-gray-300">
                    <option value="">Todas las acciones</option>
                    @foreach ($actions as $action)<option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>@endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" aria-label="Fecha inicial" class="rounded-md border-gray-300">
                <input type="date" name="date_to" value="{{ request('date_to') }}" aria-label="Fecha final" class="rounded-md border-gray-300">
                <div class="flex gap-2">
                    <button class="flex-1 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Filtrar</button>
                    <a href="{{ route('audits.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Limpiar</a>
                </div>
            </form>

            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                            <tr><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Usuario</th><th class="px-4 py-3">Módulo</th><th class="px-4 py-3">Acción</th><th class="px-4 py-3">Descripción</th><th class="px-4 py-3">IP</th><th class="px-4 py-3"></th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($audits as $audit)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $audit->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $audit->user?->name ?? 'Usuario eliminado' }}</td>
                                    <td class="px-4 py-3"><span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">{{ $audit->module }}</span></td>
                                    <td class="px-4 py-3 text-gray-700">{{ ucfirst(str_replace('_', ' ', $audit->action)) }}</td>
                                    <td class="max-w-sm truncate px-4 py-3 text-gray-600">{{ $audit->description }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $audit->ip_address ?? '—' }}</td>
                                    <td class="px-4 py-3"><a href="{{ route('audits.show', $audit) }}" class="font-medium text-indigo-600 hover:text-indigo-800">Ver detalle</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">No hay registros de auditoría para los filtros seleccionados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($audits->hasPages())<div class="border-t border-gray-200 px-4 py-3">{{ $audits->links() }}</div>@endif
            </div>
        </div>
    </div>
</x-app-layout>
