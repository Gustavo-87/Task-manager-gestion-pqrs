<x-app-layout>
    <x-slot name="header">
        <div><h1 class="text-2xl font-bold text-slate-900">Nueva PQRS</h1><p class="mt-1 text-sm text-slate-500">Registra una petición, queja, reclamo o sugerencia.</p></div>
    </x-slot>
    <div class="p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('pqrs.store') }}" class="mx-auto max-w-5xl">
            @csrf
            @include('pqrs.partials.form')
            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('pqrs.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Guardar PQRS</button>
            </div>
        </form>
    </div>
</x-app-layout>
