<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h1 class="text-2xl font-bold text-slate-900">Editar PQR #{{ $pqr->id }}</h1><p class="mt-1 text-sm text-slate-500">Actualiza la información y consulta su fecha límite.</p></div><span class="self-start rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Radicada el {{ $pqr->fecha_radicacion->format('d/m/Y') }}</span></div>
    </x-slot>
    <div class="p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('pqrs.update', $pqr) }}" class="mx-auto max-w-5xl">
            @csrf @method('PUT')
            @include('pqrs.partials.form', ['editing' => true])
            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('pqrs.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Actualizar PQR</button>
            </div>
        </form>
    </div>
</x-app-layout>
