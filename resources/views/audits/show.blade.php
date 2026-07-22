<x-app-layout>
    <x-slot name="header"><div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h1 class="text-2xl font-bold text-slate-900">Detalle de auditoría #{{ $audit->id }}</h1><p class="mt-1 text-sm text-slate-500">Registro de solo lectura para trazabilidad del sistema.</p></div><a href="{{ route('audits.index') }}" class="self-start rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">← Volver</a></div></x-slot>

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <section class="mx-auto max-w-6xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50 px-5 py-4"><div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-800">{{ $audit->module }}</span><span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ ucfirst(str_replace('_',' ',$audit->action)) }}</span></div><p class="mt-3 font-medium text-slate-800">{{ $audit->description }}</p></div>
            <div class="grid gap-px bg-slate-200 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (['Fecha y hora' => $audit->created_at->format('d/m/Y H:i:s'),'Responsable' => $audit->user?->name ?? 'Usuario eliminado','Correo' => $audit->user?->email ?? '—','Dirección IP' => $audit->ip_address ?? '—','Módulo' => $audit->module,'Acción' => ucfirst(str_replace('_',' ',$audit->action))] as $label => $value)<div class="bg-white p-5"><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</p><p class="mt-2 break-words text-sm font-medium text-slate-800">{{ $value }}</p></div>@endforeach
            </div>
            <div class="border-t border-slate-100 p-5"><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Navegador / dispositivo</p><p class="mt-2 break-all text-sm text-slate-600">{{ $audit->user_agent ?? 'Sin información disponible' }}</p></div>
        </section>

        <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-2">
            @foreach ([['title' => 'Valores anteriores','values' => $audit->old_values,'border' => 'border-amber-200','header' => 'bg-amber-50 text-amber-900'],['title' => 'Valores nuevos','values' => $audit->new_values,'border' => 'border-emerald-200','header' => 'bg-emerald-50 text-emerald-900']] as $panel)
                <section class="overflow-hidden rounded-xl border bg-white shadow-sm {{ $panel['border'] }}"><h2 class="px-5 py-4 font-semibold {{ $panel['header'] }}">{{ $panel['title'] }}</h2><div class="divide-y divide-slate-100">
                    @forelse(($panel['values'] ?? []) as $field => $value)<div class="grid gap-1 px-5 py-3 sm:grid-cols-3"><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ ucfirst(str_replace('_',' ',$field)) }}</p><p class="break-words text-sm text-slate-700 sm:col-span-2">{{ is_bool($value) ? ($value ? 'Sí' : 'No') : (is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : ($value ?? 'Vacío')) }}</p></div>@empty<div class="px-5 py-10 text-center text-sm text-slate-500">Sin valores registrados.</div>@endforelse
                </div></section>
            @endforeach
        </div>

        <div class="mx-auto max-w-6xl rounded-lg border border-sky-200 bg-sky-50 p-4 text-sm text-sky-800"><strong>Registro protegido:</strong> este evento no puede modificarse ni eliminarse desde la aplicación.</div>
    </div>
</x-app-layout>
