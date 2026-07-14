@php($editing = $editing ?? false)

<div>
    <x-input-label for="asunto" value="Asunto" />
    <x-text-input id="asunto" name="asunto" class="mt-1 block w-full" :value="old('asunto', $pqr->asunto ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('asunto')" class="mt-2" />
</div>

<div>
    <x-input-label for="descripcion" value="Descripción" />
    <textarea id="descripcion" name="descripcion" rows="5" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('descripcion', $pqr->descripcion ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
</div>

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <x-input-label for="tipo_pqr_id" value="Tipo de PQR" />
        <select id="tipo_pqr_id" name="tipo_pqr_id" required class="mt-1 block w-full rounded-md border-gray-300">
            <option value="">Seleccione...</option>
            @foreach ($tipos as $tipo)
                <option value="{{ $tipo->id }}" @selected(old('tipo_pqr_id', $pqr->tipo_pqr_id ?? '') == $tipo->id)>{{ $tipo->nombre }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('tipo_pqr_id')" class="mt-2" />
    </div>

    @if ($editing && auth()->user()->rol === 'admin')
        <div>
            <x-input-label for="estado" value="Estado" />
            <select id="estado" name="estado" required class="mt-1 block w-full rounded-md border-gray-300">
                @foreach (['radicada' => 'Radicada', 'en_revision' => 'En revisión', 'respondida' => 'Respondida', 'cerrada' => 'Cerrada'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('estado', $pqr->estado) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div>
        <x-input-label for="fecha_radicacion" value="Fecha de radicación" />
        <x-text-input id="fecha_radicacion" type="date" name="fecha_radicacion" class="mt-1 block w-full" :value="old('fecha_radicacion', $pqr->fecha_radicacion ?? now()->toDateString())" required />
        <x-input-error :messages="$errors->get('fecha_radicacion')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="fecha_limite_respuesta" value="Fecha límite de respuesta" />
        <x-text-input id="fecha_limite_respuesta" type="date" name="fecha_limite_respuesta" class="mt-1 block w-full" :value="old('fecha_limite_respuesta', $pqr->fecha_limite_respuesta ?? '')" />
        <x-input-error :messages="$errors->get('fecha_limite_respuesta')" class="mt-2" />
    </div>
</div>
