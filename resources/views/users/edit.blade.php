<x-app-layout>
    <x-slot name="header"><div><h1 class="text-2xl font-bold text-slate-900">Editar usuario</h1><p class="mt-1 text-sm text-slate-500">Actualiza la cuenta de {{ $user->name }}.</p></div></x-slot>
    <div class="p-4 sm:p-6 lg:p-8"><form method="POST" action="{{ route('users.update', $user) }}" class="mx-auto max-w-4xl">@csrf @method('PUT') @include('users.partials.form', ['user' => $user])</form></div>
</x-app-layout>
