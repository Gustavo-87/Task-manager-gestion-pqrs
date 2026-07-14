<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><h1 class="text-2xl font-bold text-slate-900">{{ Auth::user()->rol === 'admin' ? 'Gestión de PQR' : 'Mis PQR' }}</h1><p class="mt-1 text-sm text-slate-500">Consulta y administra las solicitudes registradas.</p></div><a href="{{ route('pqrs.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"><span class="mr-2 text-lg leading-none">+</span> Nueva PQR</a></div>
    </x-slot>

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        @if (session('success'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
            @foreach ([
                ['Total', $stats->total ?? 0, 'border-indigo-200 bg-indigo-50 text-indigo-700'],
                ['Radicadas', $stats->radicadas ?? 0, 'border-green-200 bg-green-50 text-green-700'],
                ['En revisión', $stats->en_revision ?? 0, 'border-yellow-200 bg-yellow-50 text-yellow-700'],
                ['Respondidas', $stats->respondidas ?? 0, 'border-orange-200 bg-orange-50 text-orange-700'],
                ['Cerradas', $stats->cerradas ?? 0, 'border-blue-200 bg-blue-50 text-blue-700'],
            ] as [$label, $value, $style])
                <div class="rounded-xl border p-4 shadow-sm {{ $style }}"><p class="text-xs font-semibold uppercase tracking-wide opacity-80">{{ $label }}</p><p class="mt-2 text-2xl font-bold">{{ $value }}</p></div>
            @endforeach
        </div>

        <form method="GET" action="{{ route('pqrs.index') }}" class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row">
            <div class="relative flex-1"><svg class="absolute left-3 top-3 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por asunto..." class="w-full rounded-lg border-slate-300 pl-10 text-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
            <select name="estado" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">Todos los estados</option>@foreach (['radicada' => 'Radicada', 'en_revision' => 'En revisión', 'respondida' => 'Respondida', 'cerrada' => 'Cerrada'] as $value => $label)<option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>@endforeach</select>
            <button class="rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">Filtrar</button>
            @if(request()->hasAny(['buscar', 'estado']))<a href="{{ route('pqrs.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">Limpiar</a>@endif
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3.5">Radicado / asunto</th><th class="px-5 py-3.5">Categoría</th><th class="px-5 py-3.5">Estado</th><th class="px-5 py-3.5">Fechas</th>@if(Auth::user()->rol === 'admin')<th class="px-5 py-3.5">Residente</th>@endif<th class="px-5 py-3.5 text-right">Acciones</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($pqrs as $pqr)
                    @php $estado = match ($pqr->estado) {'radicada' => ['Radicada','bg-green-100 text-green-800'], 'en_revision' => ['En revisión','bg-yellow-100 text-yellow-800'], 'respondida' => ['Respondida','bg-orange-100 text-orange-800'], 'cerrada' => ['Cerrada','bg-blue-100 text-blue-800'], default => [ucfirst($pqr->estado),'bg-slate-100 text-slate-700']}; @endphp
                    <tr class="hover:bg-slate-50/70"><td class="px-5 py-4"><p class="font-semibold text-slate-900">#{{ $pqr->id }} · {{ $pqr->asunto }}</p><p class="mt-1 max-w-xs truncate text-xs text-slate-500">{{ $pqr->descripcion }}</p></td><td class="px-5 py-4 text-slate-600">{{ $pqr->tipoPqr?->nombre ?? 'Sin categoría' }}</td><td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $estado[1] }}">{{ $estado[0] }}</span></td><td class="whitespace-nowrap px-5 py-4"><p class="text-slate-700">{{ $pqr->fecha_radicacion->format('d/m/Y') }}</p><p class="mt-1 text-xs {{ $pqr->fecha_limite_respuesta->isPast() && !in_array($pqr->estado, ['respondida','cerrada']) ? 'font-semibold text-rose-600' : 'text-slate-400' }}">Límite: {{ $pqr->fecha_limite_respuesta->format('d/m/Y') }}</p></td>@if(Auth::user()->rol === 'admin')<td class="px-5 py-4 text-slate-600">{{ $pqr->user?->name ?? 'Sin usuario' }}</td>@endif<td class="px-5 py-4"><div class="flex justify-end gap-3"><a href="{{ route('pqrs.edit', $pqr) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">{{ Auth::user()->rol === 'admin' && !in_array($pqr->estado, ['respondida', 'cerrada']) ? 'Responder' : 'Ver / editar' }}</a>@can('delete', $pqr)<form action="{{ route('pqrs.destroy', $pqr) }}" method="POST" onsubmit="return confirm('¿Eliminar esta PQR?')">@csrf @method('DELETE')<button class="font-semibold text-rose-600 hover:text-rose-800">Eliminar</button></form>@endcan</div></td></tr>
                @empty <tr><td colspan="6" class="px-5 py-14 text-center"><p class="font-medium text-slate-600">No hay PQR registradas</p><p class="mt-1 text-sm text-slate-400">Crea una nueva solicitud para comenzar.</p></td></tr> @endforelse
                </tbody></table></div>
            @if($pqrs->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $pqrs->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
