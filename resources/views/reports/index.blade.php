<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">Reportes de PQR</h2></x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            <div class="rounded-lg bg-white p-6 shadow">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Generar reporte</h3>
                    <p class="mt-1 text-sm text-gray-600">Selecciona el periodo de radicación que deseas analizar.</p>
                </div>

                <form method="POST" class="space-y-6" x-data="{ action: '' }">
                    @csrf
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="date_from" value="Fecha inicial" />
                            <x-text-input id="date_from" type="date" name="date_from" class="mt-1 block w-full" :value="old('date_from')" required />
                            <x-input-error :messages="$errors->get('date_from')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="date_to" value="Fecha final" />
                            <x-text-input id="date_to" type="date" name="date_to" class="mt-1 block w-full" :value="old('date_to')" required />
                            <x-input-error :messages="$errors->get('date_to')" class="mt-2" />
                        </div>
                    </div>

                    <div class="rounded-md bg-blue-50 p-4 text-sm text-blue-800">
                        El PDF incluirá resumen por estado y categoría, detalle de PQR, casos vencidos y casos que vencen al día siguiente.
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="submit" formaction="{{ route('reports.download') }}" @click="action = 'download'" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            Descargar PDF
                        </button>
                        <button type="submit" formaction="{{ route('reports.email') }}" @click="action = 'email'" class="rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                            Enviar a mi correo
                        </button>
                    </div>
                    <p class="text-xs text-gray-500">El envío se realizará a {{ auth()->user()->email }}.</p>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
