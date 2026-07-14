<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">Editar usuario</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('users.update', $user) }}" class="rounded-lg bg-white p-6 shadow">
                @csrf @method('PUT')
                @include('users.partials.form', ['user' => $user])
            </form>
        </div>
    </div>
</x-app-layout>
