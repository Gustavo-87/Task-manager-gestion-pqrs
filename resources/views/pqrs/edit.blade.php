<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h1 class="text-2xl font-bold text-slate-900">Editar PQR #{{ $pqr->id }}</h1><p class="mt-1 text-sm text-slate-500">Actualiza la información y consulta su fecha límite.</p></div><span class="self-start rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Radicada el {{ $pqr->fecha_radicacion->format('d/m/Y') }}</span></div>
    </x-slot>
    <div class="p-4 sm:p-6 lg:p-8">
        @if (session('success'))<div class="mx-auto mb-5 max-w-5xl rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
        <form method="POST" action="{{ route('pqrs.update', $pqr) }}" class="mx-auto max-w-5xl">
            @csrf @method('PUT')
            @include('pqrs.partials.form', ['editing' => true])
            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('pqrs.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Actualizar PQR</button>
            </div>
        </form>

        <section class="mx-auto mt-6 max-w-5xl rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-semibold text-slate-900">Respuesta a la PQR</h2>
                <p class="mt-1 text-sm text-slate-500">La respuesta oficial queda registrada junto con el responsable y la fecha.</p>
            </div>

            @if ($pqr->respuesta)
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                    <p class="whitespace-pre-line text-sm leading-6 text-slate-800">{{ $pqr->respuesta }}</p>
                    <p class="mt-4 border-t border-emerald-200 pt-3 text-xs text-emerald-800">
                        Respondida por {{ $pqr->responder?->name ?? 'Usuario no disponible' }}
                        @if($pqr->respondida_en) el {{ $pqr->respondida_en->format('d/m/Y \a \l\a\s H:i') }} @endif
                    </p>
                </div>
            @else
                @can('respond', $pqr)
                    <form method="POST" action="{{ route('pqrs.respond', $pqr) }}" class="mt-5">
                        @csrf
                        <label for="respuesta" class="block text-sm font-semibold text-slate-700">Respuesta <span class="text-rose-500">*</span></label>
                        <textarea id="respuesta" name="respuesta" rows="7" required maxlength="10000" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Escribe la respuesta clara y completa que recibirá el residente...">{{ old('respuesta') }}</textarea>
                        <x-input-error :messages="$errors->get('respuesta')" class="mt-2" />
                        <div class="mt-4 flex justify-end">
                            <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700" onclick="return confirm('¿Confirmas que deseas emitir esta respuesta?')">Responder PQR</button>
                        </div>
                    </form>
                @else
                    <p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">Esta PQR aún no ha recibido respuesta.</p>
                @endcan
            @endif
        </section>
    </div>
</x-app-layout>
