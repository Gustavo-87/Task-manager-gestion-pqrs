<x-app-layout>
    @php($hasResponse = filled($pqr->respuesta))
    @php($legacyMissingResponse = in_array($pqr->estado, ['resuelta', 'cerrada', 'respondida'], true) && ! $hasResponse)
    @php($workflowActions = \App\Models\Pqr::workflowActionsFor($pqr->estado))
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h1 class="text-2xl font-bold text-slate-900">Detalle de PQRS #{{ $pqr->id }}</h1><p class="mt-1 text-sm text-slate-500">Consulta la solicitud radicada y gestiona la respuesta administrativa.</p></div><span class="self-start rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Radicada el {{ $pqr->fecha_radicacion->format('d/m/Y') }}</span></div>
    </x-slot>
    <div class="p-4 sm:p-6 lg:p-8">
        @if (session('success'))<div class="mx-auto mb-5 max-w-5xl rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
        <div class="mx-auto max-w-5xl">
            @include('pqrs.partials.form', ['editing' => true])
            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('pqrs.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
            </div>
        </div>

        @can('manageWorkflow', $pqr)
            <section class="mx-auto mt-6 max-w-5xl rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Flujo de atención</h2>
                    <p class="mt-1 text-sm text-slate-500">Ejecuta la siguiente transición disponible según el estado actual de la PQRS.</p>
                </div>

                @if ($workflowActions !== [])
                    <div class="mt-5 flex flex-wrap gap-3">
                        @foreach ($workflowActions as $action => $transition)
                            <form method="POST" action="{{ route('pqrs.workflow.transition', $pqr) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="action" value="{{ $action }}">
                                <button class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100" onclick="return confirm('¿Confirmas cambiar el estado a {{ \App\Models\Pqr::statusLabel($transition['to']) }}?')">{{ $transition['label'] }}</button>
                            </form>
                        @endforeach
                    </div>
                @else
                    <p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">No hay transiciones manuales disponibles para este estado.</p>
                @endif
            </section>
        @endcan

        <section class="mx-auto mt-6 max-w-5xl rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-semibold text-slate-900">Respuesta a la PQRS</h2>
                <p class="mt-1 text-sm text-slate-500">La respuesta oficial queda registrada junto con el responsable y la fecha. El flujo normal la resuelve desde el estado En proceso.</p>
            </div>

            @if ($hasResponse)
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                    <p class="whitespace-pre-line text-sm leading-6 text-slate-800">{{ $pqr->respuesta }}</p>
                    <p class="mt-4 border-t border-emerald-200 pt-3 text-xs text-emerald-800">
                        Respuesta registrada por {{ $pqr->responder?->name ?? 'Usuario no disponible' }}
                        @if($pqr->respondida_en) el {{ $pqr->respondida_en->format('d/m/Y \a \l\a\s H:i') }} @endif
                    </p>
                </div>
                @can('updateResponse', $pqr)
                    <form method="POST" action="{{ route('pqrs.response.update', $pqr) }}" class="mt-4 rounded-lg border border-indigo-200 bg-indigo-50 p-4">
                        @csrf
                        @method('PATCH')
                        <label for="respuesta_editar" class="block text-sm font-semibold text-slate-700">Editar respuesta <span class="text-rose-500">*</span></label>
                        <textarea id="respuesta_editar" name="respuesta" rows="7" required maxlength="10000" class="mt-2 block w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('respuesta', $pqr->respuesta) }}</textarea>
                        <x-input-error :messages="$errors->get('respuesta')" class="mt-2" />
                        <p class="mt-2 text-xs text-indigo-700">La versión anterior permanecerá disponible en el historial de la PQRS.</p>
                        <div class="mt-4 flex justify-end">
                            <button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700" onclick="return confirm('¿Confirmas que deseas actualizar la respuesta?')">Guardar cambios</button>
                        </div>
                    </form>
                @endcan
            @else
                @if ($legacyMissingResponse)
                    <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        <p class="font-semibold">Respuesta faltante en un registro previo</p>
                        <p class="mt-1">La PQRS figura como {{ $pqr->estado === 'cerrada' ? 'cerrada' : 'resuelta' }}, pero no tiene texto de respuesta registrado. Puedes completar la respuesta oficial para dejar el expediente consistente.</p>
                    </div>
                @endif

                @can('respond', $pqr)
                    <form method="POST" action="{{ route('pqrs.respond', $pqr) }}" class="mt-5">
                        @csrf
                        <label for="respuesta" class="block text-sm font-semibold text-slate-700">{{ $legacyMissingResponse ? 'Registrar respuesta faltante' : 'Respuesta de resolución' }} <span class="text-rose-500">*</span></label>
                        <textarea id="respuesta" name="respuesta" rows="7" required maxlength="10000" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Escribe la respuesta clara y completa que recibirá el residente...">{{ old('respuesta') }}</textarea>
                        <x-input-error :messages="$errors->get('respuesta')" class="mt-2" />
                        @if ($legacyMissingResponse)
                            <p class="mt-2 text-xs text-amber-800">Esta acción no reabre la PQRS. Solo completa la respuesta que faltaba en el registro anterior.</p>
                        @else
                            <p class="mt-2 text-xs text-indigo-700">Al guardar esta respuesta, la PQRS cambiará automáticamente al estado Resuelta.</p>
                        @endif
                        <div class="mt-4 flex justify-end">
                            <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700" onclick="return confirm('¿Confirmas que deseas emitir esta respuesta?')">{{ $legacyMissingResponse ? 'Registrar respuesta faltante' : 'Resolver PQRS' }}</button>
                        </div>
                    </form>
                @else
                    <p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">{{ $legacyMissingResponse ? 'La administración aún no completa el texto de respuesta de esta PQRS.' : 'La respuesta se habilita cuando la PQRS llegue al estado En proceso.' }}</p>
                @endcan
            @endif
        </section>
    </div>
</x-app-layout>
