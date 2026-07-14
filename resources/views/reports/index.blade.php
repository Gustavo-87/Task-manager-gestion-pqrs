<x-app-layout>
    <x-slot name="header"><div><h1 class="text-2xl font-bold text-slate-900">Reportes de PQR</h1><p class="mt-1 text-sm text-slate-500">Analiza la gestión y genera documentos PDF por periodo.</p></div></x-slot>

    <div class="space-y-6 p-8">
        @if(session('success'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800">{{ $errors->first() }}</div>@endif

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4"><h2 class="text-lg font-semibold text-slate-900">Generar reporte</h2><p class="mt-1 text-sm text-slate-500">Selecciona el periodo que deseas analizar.</p></div>
            <div class="flex items-end gap-4">
                <form method="GET" action="{{ route('reports.index') }}" class="flex flex-1 items-end gap-4">
                    <div class="flex-1"><label for="date_from" class="block text-sm font-semibold text-slate-700">Fecha inicial</label><input id="date_from" type="date" name="date_from" value="{{ $dateFrom }}" required class="mt-2 block w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                    <div class="flex-1"><label for="date_to" class="block text-sm font-semibold text-slate-700">Fecha final</label><input id="date_to" type="date" name="date_to" value="{{ $dateTo }}" required class="mt-2 block w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                    <button class="rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">Actualizar vista previa</button>
                </form>
                <form method="POST" action="{{ route('reports.download') }}">@csrf<input type="hidden" name="date_from" value="{{ $dateFrom }}"><input type="hidden" name="date_to" value="{{ $dateTo }}"><button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Descargar PDF</button></form>
                <form method="POST" action="{{ route('reports.email') }}">@csrf<input type="hidden" name="date_from" value="{{ $dateFrom }}"><input type="hidden" name="date_to" value="{{ $dateTo }}"><button class="rounded-lg border border-indigo-300 bg-indigo-50 px-5 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">Enviar por correo</button></form>
            </div>
            <div class="mt-4 flex items-center justify-between rounded-lg bg-sky-50 px-4 py-3 text-sm text-sky-800"><span>Periodo seleccionado: <strong>{{ $preview['dateFrom']->format('d/m/Y') }}</strong> al <strong>{{ $preview['dateTo']->format('d/m/Y') }}</strong></span><span>Destinatario: <strong>{{ auth()->user()->email }}</strong></span></div>
        </section>

        <div class="grid grid-cols-4 gap-4">
            @foreach ([['PQR del periodo',$preview['pqrs']->count(),'border-indigo-200 bg-indigo-50 text-indigo-700'],['Vencidas',$preview['overdue']->count(),'border-rose-200 bg-rose-50 text-rose-700'],['Próximas a vencer',$preview['upcoming']->count(),'border-amber-200 bg-amber-50 text-amber-700'],['Porcentaje atendido',$attendedPercentage.'%','border-emerald-200 bg-emerald-50 text-emerald-700']] as $card)<div class="rounded-xl border p-5 shadow-sm {{ $card[2] }}"><p class="text-xs font-semibold uppercase tracking-wide opacity-80">{{ $card[0] }}</p><p class="mt-2 text-3xl font-bold">{{ $card[1] }}</p></div>@endforeach
        </div>

        <div class="grid grid-cols-2 gap-6">
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-900">Resumen por estado</h2><p class="mt-1 text-xs text-slate-500">Distribución de las PQR incluidas en el periodo.</p></div><div class="space-y-4 p-5">
                @foreach(['radicada' => ['Radicadas','bg-green-500','text-green-700'],'en_revision' => ['En revisión','bg-yellow-500','text-yellow-700'],'respondida' => ['Respondidas','bg-orange-500','text-orange-700'],'cerrada' => ['Cerradas','bg-blue-500','text-blue-700']] as $status => [$label,$bar,$text])
                    @php $value = $preview['byStatus'][$status]; $percentage = $preview['pqrs']->isEmpty() ? 0 : round(($value / $preview['pqrs']->count()) * 100); @endphp
                    <div><div class="mb-1.5 flex justify-between text-sm"><span class="font-medium {{ $text }}">{{ $label }}</span><span class="font-semibold text-slate-700">{{ $value }} <span class="font-normal text-slate-400">({{ $percentage }}%)</span></span></div><div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $bar }}" style="width: {{ $percentage }}%"></div></div></div>
                @endforeach
            </div></section>

            <section class="rounded-xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-900">Resumen por categoría</h2><p class="mt-1 text-xs text-slate-500">Cantidad de solicitudes clasificadas.</p></div><div class="divide-y divide-slate-100">
                @forelse($preview['byCategory'] as $category => $total)<div class="flex items-center justify-between px-5 py-3"><span class="text-sm font-medium text-slate-700">{{ $category }}</span><span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-bold text-indigo-700">{{ $total }}</span></div>@empty<div class="px-5 py-10 text-center text-sm text-slate-500">Sin datos en el periodo.</div>@endforelse
            </div></section>
        </div>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-900">Vista previa de PQR</h2><p class="mt-1 text-xs text-slate-500">Primeros 10 registros que aparecerán en el reporte.</p></div><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Radicado</th><th class="px-5 py-3">Asunto</th><th class="px-5 py-3">Categoría</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3">Fecha límite</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($preview['pqrs']->take(10) as $pqr)<tr><td class="px-5 py-3 font-semibold text-slate-700">#{{ $pqr->id }}</td><td class="max-w-sm truncate px-5 py-3 text-slate-700">{{ $pqr->asunto }}</td><td class="px-5 py-3 text-slate-500">{{ $pqr->tipoPqr?->nombre ?? 'Sin categoría' }}</td><td class="px-5 py-3 text-slate-600">{{ match($pqr->estado){'radicada'=>'Radicada','en_revision'=>'En revisión','respondida'=>'Respondida','cerrada'=>'Cerrada',default=>$pqr->estado} }}</td><td class="px-5 py-3 text-slate-600">{{ $pqr->fecha_limite_respuesta->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">No hay PQR en este periodo.</td></tr>@endforelse</tbody></table></section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-900">Actividad reciente de reportes</h2></div><div class="divide-y divide-slate-100">@forelse($recentReports as $event)<div class="flex items-center justify-between px-5 py-3"><div><p class="text-sm font-medium text-slate-700">{{ $event->description }}</p><p class="mt-1 text-xs text-slate-400">{{ $event->user?->name ?? 'Usuario eliminado' }}</p></div><span class="text-xs text-slate-500">{{ $event->created_at->format('d/m/Y H:i') }}</span></div>@empty<div class="px-5 py-8 text-center text-sm text-slate-500">Aún no se han descargado o enviado reportes.</div>@endforelse</div></section>
    </div>
</x-app-layout>
