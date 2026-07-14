<x-app-layout>
    <x-slot name="header"><div><h1 class="text-2xl font-bold text-slate-900">Auditoría del sistema</h1><p class="mt-1 text-sm text-slate-500">Consulta la trazabilidad de acciones realizadas en la plataforma.</p></div></x-slot>

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([['Eventos registrados',$stats['total'],'border-indigo-200 bg-indigo-50 text-indigo-700'],['Eventos de hoy',$stats['today'],'border-emerald-200 bg-emerald-50 text-emerald-700'],['Usuarios identificados',$stats['users'],'border-violet-200 bg-violet-50 text-violet-700'],['PQR auditadas',$stats['pqrs'],'border-sky-200 bg-sky-50 text-sky-700']] as $card)<div class="rounded-xl border p-4 shadow-sm {{ $card[2] }}"><p class="text-xs font-semibold uppercase tracking-wide opacity-80">{{ $card[0] }}</p><p class="mt-2 text-2xl font-bold">{{ $card[1] }}</p></div>@endforeach
        </div>

        <form method="GET" action="{{ route('audits.index') }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="relative md:col-span-2"><svg class="absolute left-3 top-3 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><input name="buscar" value="{{ request('buscar') }}" placeholder="Buscar en la descripción..." class="w-full rounded-lg border-slate-300 pl-10 text-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                <select name="user_id" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">Todos los usuarios</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((string)request('user_id') === (string)$user->id)>{{ $user->name }}</option>@endforeach</select>
                <select name="module" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">Todos los módulos</option>@foreach($modules as $module)<option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>@endforeach</select>
                <select name="action" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">Todas las acciones</option>@foreach($actions as $action)<option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst(str_replace('_',' ',$action)) }}</option>@endforeach</select>
                <div><label for="date_from" class="mb-1 block text-xs font-semibold text-slate-500">Desde</label><input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                <div><label for="date_to" class="mb-1 block text-xs font-semibold text-slate-500">Hasta</label><input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                <div class="flex items-end gap-2"><button class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">Filtrar</button><a href="{{ route('audits.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Limpiar</a></div>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3.5">Fecha y responsable</th><th class="px-5 py-3.5">Módulo</th><th class="px-5 py-3.5">Acción</th><th class="px-5 py-3.5">Descripción</th><th class="px-5 py-3.5">IP</th><th class="px-5 py-3.5"></th></tr></thead><tbody class="divide-y divide-slate-100">
                @forelse($audits as $audit)
                    @php
                        $actionClass = match(true) { str_contains($audit->action,'eliminar') || str_contains($audit->action,'fall') => 'bg-rose-100 text-rose-800', str_contains($audit->action,'crear') || str_contains($audit->action,'enviar') => 'bg-emerald-100 text-emerald-800', str_contains($audit->action,'actualizar') || str_contains($audit->action,'cambiar') => 'bg-amber-100 text-amber-800', str_contains($audit->action,'descargar') || str_contains($audit->action,'generar') => 'bg-sky-100 text-sky-800', default => 'bg-slate-100 text-slate-700' };
                        $moduleClass = match($audit->module) { 'PQR' => 'bg-indigo-100 text-indigo-800', 'Usuarios' => 'bg-violet-100 text-violet-800', 'Correo' => 'bg-emerald-100 text-emerald-800', 'Reportes' => 'bg-sky-100 text-sky-800', default => 'bg-slate-100 text-slate-700' };
                    @endphp
                    <tr class="hover:bg-slate-50/70"><td class="whitespace-nowrap px-5 py-4"><p class="font-medium text-slate-800">{{ $audit->created_at->format('d/m/Y') }} <span class="font-normal text-slate-400">{{ $audit->created_at->format('H:i:s') }}</span></p><p class="mt-1 text-xs text-slate-500">{{ $audit->user?->name ?? 'Usuario eliminado' }}</p></td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $moduleClass }}">{{ $audit->module }}</span></td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $actionClass }}">{{ ucfirst(str_replace('_',' ',$audit->action)) }}</span></td><td class="max-w-sm px-5 py-4"><p class="truncate text-slate-600" title="{{ $audit->description }}">{{ $audit->description }}</p></td><td class="whitespace-nowrap px-5 py-4 font-mono text-xs text-slate-500">{{ $audit->ip_address ?? '—' }}</td><td class="px-5 py-4 text-right"><a href="{{ route('audits.show',$audit) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Ver detalle</a></td></tr>
                @empty<tr><td colspan="6" class="px-5 py-14 text-center"><p class="font-medium text-slate-600">No hay eventos para los filtros seleccionados</p><p class="mt-1 text-sm text-slate-400">Prueba con otros criterios de búsqueda.</p></td></tr>@endforelse
            </tbody></table></div>
            @if($audits->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $audits->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
