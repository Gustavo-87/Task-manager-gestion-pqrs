<x-app-layout>
    <x-slot name="header">
        <div><h1 class="text-2xl font-bold text-slate-900">Panel principal</h1><p class="mt-1 text-sm text-slate-500">Resumen general de las PQRS y sus vencimientos.</p></div>
    </x-slot>

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7">
            @foreach ([
                ['label' => 'Total de PQRS', 'value' => $counts->total ?? 0, 'card' => 'border-indigo-200 bg-indigo-50', 'text' => 'text-indigo-700', 'iconBg' => 'bg-indigo-100 text-indigo-700', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414A1 1 0 0117 7.414V19a2 2 0 01-2 2z'],
                ['label' => 'Radicadas', 'value' => $counts->radicadas ?? 0, 'card' => 'border-green-200 bg-green-50', 'text' => 'text-green-700', 'iconBg' => 'bg-green-100 text-green-700', 'icon' => 'M12 4v16m8-8H4'],
                ['label' => 'En revisión', 'value' => $counts->en_revision ?? 0, 'card' => 'border-yellow-200 bg-yellow-50', 'text' => 'text-yellow-700', 'iconBg' => 'bg-yellow-100 text-yellow-700', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'En proceso', 'value' => $counts->en_proceso ?? 0, 'card' => 'border-violet-200 bg-violet-50', 'text' => 'text-violet-700', 'iconBg' => 'bg-violet-100 text-violet-700', 'icon' => 'M12 6v6l4 2'],
                ['label' => 'En espera', 'value' => $counts->en_espera ?? 0, 'card' => 'border-amber-200 bg-amber-50', 'text' => 'text-amber-700', 'iconBg' => 'bg-amber-100 text-amber-700', 'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Rechazadas', 'value' => $counts->rechazadas ?? 0, 'card' => 'border-rose-200 bg-rose-50', 'text' => 'text-rose-700', 'iconBg' => 'bg-rose-100 text-rose-700', 'icon' => 'M6 18L18 6M6 6l12 12'],
                ['label' => 'Resueltas', 'value' => $counts->resueltas ?? 0, 'card' => 'border-orange-200 bg-orange-50', 'text' => 'text-orange-700', 'iconBg' => 'bg-orange-100 text-orange-700', 'icon' => 'M5 13l4 4L19 7'],
                ['label' => 'Cerradas', 'value' => $counts->cerradas ?? 0, 'card' => 'border-blue-200 bg-blue-50', 'text' => 'text-blue-700', 'iconBg' => 'bg-blue-100 text-blue-700', 'icon' => 'M5 13l4 4L19 7'],
            ] as $card)
                <div class="rounded-xl border p-4 shadow-sm {{ $card['card'] }}">
                    <div class="flex items-center justify-between gap-2"><div><p class="text-xs font-semibold uppercase tracking-wide {{ $card['text'] }}">{{ $card['label'] }}</p><p class="mt-2 text-3xl font-bold {{ $card['text'] }}">{{ $card['value'] }}</p></div><div class="rounded-xl p-2.5 {{ $card['iconBg'] }}"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/></svg></div></div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4"><div><h2 class="font-semibold text-slate-900">PQRS recientes</h2><p class="text-xs text-slate-500">Últimos registros del sistema</p></div><a href="{{ route('pqrs.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Ver todas</a></div>
                <div class="divide-y divide-slate-100">
                    @forelse ($recent as $pqr)
                        <a href="{{ route('pqrs.edit', $pqr) }}" class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-slate-50"><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-800">{{ $pqr->asunto }}</p><p class="mt-1 text-xs text-slate-500">{{ $pqr->tipoPqr?->nombre ?? 'Sin categoría' }} · {{ $pqr->fecha_radicacion->format('d/m/Y') }}</p></div><span class="shrink-0 text-xs font-medium text-slate-500">#{{ $pqr->id }}</span></a>
                    @empty <p class="px-5 py-10 text-center text-sm text-slate-500">No hay PQRS registradas.</p> @endforelse
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-900">Próximas a vencer</h2><p class="text-xs text-slate-500">Vencen hoy o mañana</p></div>
                <div class="divide-y divide-slate-100">
                    @forelse ($upcoming as $pqr)
                        <div class="px-5 py-4"><p class="text-sm font-semibold text-slate-800">#{{ $pqr->id }} · {{ $pqr->asunto }}</p><p class="mt-1 text-xs font-medium text-rose-600">{{ \App\Models\Pqr::statusLabel($pqr->estado) }} · vence {{ $pqr->fecha_limite_respuesta->isToday() ? 'hoy' : 'mañana' }}</p></div>
                    @empty <div class="px-5 py-10 text-center"><p class="text-sm font-medium text-slate-600">Sin alertas próximas</p><p class="mt-1 text-xs text-slate-400">No hay PQRS por vencer hoy o mañana.</p></div> @endforelse
                </div>
            </section>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('pqrs.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Nueva PQRS</a>
            <a href="{{ route('pqrs.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Consultar PQRS</a>
            @if (Auth::user()->rol === 'admin')<a href="{{ route('reports.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Generar reporte</a>@endif
        </div>
    </div>
</x-app-layout>
