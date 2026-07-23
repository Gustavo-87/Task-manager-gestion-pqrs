<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">Configuración</h2></x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))<div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>@endif
            @if ($errors->any())<div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif

            <div class="grid gap-6 lg:grid-cols-3">
                <form method="POST" action="{{ route('configuration.update') }}" class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                    @csrf @method('PUT')
                    <h3 class="text-lg font-semibold text-gray-900">Datos del conjunto</h3>
                    <p class="mt-1 text-sm text-gray-500">Esta información aparecerá en los reportes y comunicaciones.</p>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2"><x-input-label for="residential_name" value="Nombre del conjunto" /><x-text-input id="residential_name" name="residential_name" class="mt-1 block w-full" :value="old('residential_name', $settings->residential_name)" required /><x-input-error :messages="$errors->get('residential_name')" class="mt-2" /></div>
                        <div><x-input-label for="nit" value="NIT (opcional)" /><x-text-input id="nit" name="nit" class="mt-1 block w-full" :value="old('nit', $settings->nit)" /></div>
                        <div><x-input-label for="phone" value="Teléfono" /><x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $settings->phone)" /></div>
                        <div class="sm:col-span-2"><x-input-label for="address" value="Dirección" /><x-text-input id="address" name="address" class="mt-1 block w-full" :value="old('address', $settings->address)" /></div>
                        <div class="sm:col-span-2"><x-input-label for="email" value="Correo de contacto" /><x-text-input id="email" type="email" name="email" class="mt-1 block w-full" :value="old('email', $settings->email)" /><x-input-error :messages="$errors->get('email')" class="mt-2" /></div>
                    </div>
                    <div class="mt-6"><x-primary-button>Guardar configuración</x-primary-button></div>
                </form>

                <div class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="font-semibold text-gray-900">Plazo de respuesta</h3>
                        <div class="mt-3 text-3xl font-bold text-indigo-700">{{ $settings->response_days }} días</div>
                        <p class="mt-1 text-sm text-gray-500">Días calendario desde la radicación.</p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="flex items-center justify-between border-b border-gray-200 p-6">
                    <div><h3 class="text-lg font-semibold text-gray-900">Categorías de PQRS</h3><p class="mt-1 text-sm text-gray-500">Administra las opciones disponibles para clasificar solicitudes.</p></div>
                    <a href="{{ route('categories.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Nueva categoría</a>
                </div>
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500"><tr><th class="px-6 py-3">Categoría</th><th class="px-6 py-3">Descripción</th><th class="px-6 py-3">Estado</th><th class="px-6 py-3">PQRS</th><th class="px-6 py-3">Acciones</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse ($categories as $category)
                        <tr><td class="px-6 py-4 font-medium text-gray-900">{{ $category->nombre }}</td><td class="max-w-md px-6 py-4 text-gray-600">{{ $category->descripcion ?: 'Sin descripción' }}</td><td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $category->activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">{{ $category->activo ? 'Activa' : 'Inactiva' }}</span></td><td class="px-6 py-4 text-gray-600">{{ $category->pqrs_count }}</td><td class="px-6 py-4"><div class="flex gap-3"><a href="{{ route('categories.edit', $category) }}" class="font-medium text-indigo-600 hover:text-indigo-800">Editar</a>@if ($category->pqrs_count === 0)<form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('¿Eliminar esta categoría?')">@csrf @method('DELETE')<button class="font-medium text-red-600 hover:text-red-800">Eliminar</button></form>@endif</div></td></tr>
                    @empty<tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">No hay categorías registradas.</td></tr>@endforelse
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</x-app-layout>
