<x-app-layout>
    <x-slot name="header"><div><h1 class="text-2xl font-bold text-slate-900">Crear usuario</h1><p class="mt-1 text-sm text-slate-500">Registra una nueva cuenta y define sus permisos.</p></div></x-slot>
    <div class="p-4 sm:p-6 lg:p-8"><form method="POST" action="{{ route('users.store') }}" class="mx-auto max-w-4xl">@csrf @include('users.partials.form', ['user' => null])</form></div>
</x-app-layout>
