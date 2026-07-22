@php($editing = $editing ?? false)
@php($currentStatus = $pqr->estado ?? 'radicada')

@if ($errors->any())
    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
        <p class="font-semibold">Revisa la información ingresada.</p>
        <p class="mt-1">Hay campos pendientes o con datos incorrectos.</p>
    </div>
@endif

<div class="grid gap-6 lg:grid-cols-3" x-data="{
    radicacion: @js(old('fecha_radicacion', isset($pqr) ? $pqr->fecha_radicacion->toDateString() : now()->toDateString())),
    estado: @js($currentStatus),
    get limite() {
        if (!this.radicacion) return '';
        const fecha = new Date(this.radicacion + 'T00:00:00');
        fecha.setDate(fecha.getDate() + 15);
        return fecha.toLocaleDateString('es-CO', { year: 'numeric', month: '2-digit', day: '2-digit' });
    },
    estadoClase() {
        return { radicada: 'bg-green-100 text-green-800', en_revision: 'bg-yellow-100 text-yellow-800', en_proceso: 'bg-violet-100 text-violet-800', en_espera: 'bg-amber-100 text-amber-800', rechazada: 'bg-rose-100 text-rose-800', resuelta: 'bg-orange-100 text-orange-800', cerrada: 'bg-blue-100 text-blue-800' }[this.estado] || 'bg-slate-100 text-slate-700';
    }
}">
    <section class="space-y-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:col-span-2">
        <div class="border-b border-slate-100 pb-4"><h2 class="text-lg font-semibold text-slate-900">Información de la solicitud</h2><p class="mt-1 text-sm text-slate-500">Describe claramente la situación para facilitar su gestión.</p></div>

        <div>
            <label for="asunto" class="block text-sm font-semibold text-slate-700">Asunto <span class="text-rose-500">*</span></label>
            <input id="asunto" name="asunto" value="{{ old('asunto', $pqr->asunto ?? '') }}" required autofocus maxlength="150" placeholder="Ejemplo: Falla en la iluminación del parqueadero" @readonly($editing) class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm read-only:bg-slate-100 read-only:text-slate-600 focus:border-indigo-500 focus:ring-indigo-500">
            <div class="mt-1 flex justify-between gap-3"><p class="text-xs text-slate-500">Resume el motivo principal de la solicitud.</p><p class="text-xs text-slate-400">Máximo 150 caracteres</p></div>
            <x-input-error :messages="$errors->get('asunto')" class="mt-2" />
        </div>

        <div>
            <label for="descripcion" class="block text-sm font-semibold text-slate-700">Descripción <span class="text-rose-500">*</span></label>
            <textarea id="descripcion" name="descripcion" rows="7" required placeholder="Explica detalladamente lo ocurrido, dónde sucedió y cualquier información relevante..." @readonly($editing) class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm read-only:bg-slate-100 read-only:text-slate-600 focus:border-indigo-500 focus:ring-indigo-500">{{ old('descripcion', $pqr->descripcion ?? '') }}</textarea>
            <p class="mt-1 text-xs text-slate-500">Incluye la información necesaria para comprender y atender la solicitud.</p>
            <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
        </div>

        <div>
            <label for="tipo_pqr_id" class="block text-sm font-semibold text-slate-700">Categoría <span class="text-rose-500">*</span></label>
            <select id="tipo_pqr_id" name="tipo_pqr_id" required @disabled($editing) class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm disabled:bg-slate-100 disabled:text-slate-600 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Seleccione una categoría...</option>
                @foreach ($tipos as $tipo)<option value="{{ $tipo->id }}" @selected(old('tipo_pqr_id', $pqr->tipo_pqr_id ?? '') == $tipo->id)>{{ $tipo->nombre }}</option>@endforeach
            </select>
            <x-input-error :messages="$errors->get('tipo_pqr_id')" class="mt-2" />
        </div>
    </section>

    <aside class="space-y-5">
        @if ($editing && isset($pqr))
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3"><h2 class="font-semibold text-slate-900">Estado</h2><span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="estadoClase()" x-text="{radicada:'Radicada',en_revision:'En revisión',en_proceso:'En proceso',en_espera:'En espera',rechazada:'Rechazada',resuelta:'Resuelta',cerrada:'Cerrada'}[estado]"></span></div>
                <p class="mt-4 text-sm text-slate-600">La PQRS radicada por el usuario es de solo lectura. La administración puede gestionar su estado según el flujo y registrar una respuesta oficial.</p>
            </section>
        @endif

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-900">Fechas de atención</h2>
            <div class="mt-4">
                <label for="fecha_radicacion" class="block text-sm font-medium text-slate-700">Fecha de radicación</label>
                <input id="fecha_radicacion" type="date" name="fecha_radicacion" x-model="radicacion" required @readonly($editing) class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm read-only:bg-slate-100 read-only:text-slate-600 focus:border-indigo-500 focus:ring-indigo-500">
                <x-input-error :messages="$errors->get('fecha_radicacion')" class="mt-2" />
            </div>
            <div class="mt-4 rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Fecha límite de respuesta</p>
                <p class="mt-1 text-lg font-bold text-indigo-900" x-text="limite"></p>
                <p class="mt-1 text-xs text-indigo-700">Se calcula automáticamente a 15 días calendario.</p>
            </div>
        </section>

        <section class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-800">
            <p class="font-semibold">Información importante</p>
            <p class="mt-1 text-xs leading-5">Los campos marcados con * son obligatorios. Podrás consultar el avance desde el módulo de PQRS.</p>
        </section>
    </aside>
</div>
