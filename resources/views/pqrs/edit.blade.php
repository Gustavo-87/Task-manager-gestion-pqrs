<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">Editar PQR</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('pqrs.update', $pqr) }}" class="space-y-5 rounded-lg bg-white p-6 shadow">
            @csrf @method('PUT')
            @include('pqrs.partials.form', ['editing' => true])
            <div class="flex gap-3">
                <x-primary-button>Actualizar PQR</x-primary-button>
                <a href="{{ route('pqrs.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Cancelar</a>
            </div>
        </form>
    </div></div>
</x-app-layout>
