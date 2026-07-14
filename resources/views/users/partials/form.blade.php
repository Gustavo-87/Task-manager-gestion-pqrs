<div class="space-y-6">
    <div>
        <x-input-label for="name" value="Nombre" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Correo electrónico" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user?->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="rol" value="Rol" />
        <select id="rol" name="rol" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="residente" @selected(old('rol', $user?->rol ?? 'residente') === 'residente')>Residente</option>
            <option value="admin" @selected(old('rol', $user?->rol) === 'admin')>Administrador</option>
        </select>
        <x-input-error :messages="$errors->get('rol')" class="mt-2" />
    </div>

    @if ($user)
        <label class="flex items-center gap-3">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" @checked(old('activo', $user->activo)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Cuenta activa</span>
        </label>
        <x-input-error :messages="$errors->get('activo')" class="mt-2" />
    @endif

    <div>
        <x-input-label for="password" :value="$user ? 'Nueva contraseña (opcional)' : 'Contraseña'" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! $user" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Confirmar contraseña" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="! $user" autocomplete="new-password" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ $user ? 'Guardar cambios' : 'Crear usuario' }}</x-primary-button>
        <a href="{{ route('users.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancelar</a>
    </div>
</div>
