<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><h1 class="text-2xl font-bold text-slate-900">Gestión de usuarios</h1><p class="mt-1 text-sm text-slate-500">Administra accesos, roles y estado de las cuentas.</p></div><a href="{{ route('users.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"><span class="mr-2 text-lg">+</span> Nuevo usuario</a></div>
    </x-slot>

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        @if (session('success'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800">{{ $errors->first() }}</div>@endif

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([
                ['Total', $stats->total ?? 0, 'border-indigo-200 bg-indigo-50 text-indigo-700'],
                ['Administradores', $stats->admins ?? 0, 'border-violet-200 bg-violet-50 text-violet-700'],
                ['Residentes', $stats->residentes ?? 0, 'border-sky-200 bg-sky-50 text-sky-700'],
                ['Inactivos', $stats->inactivos ?? 0, 'border-slate-300 bg-slate-100 text-slate-700'],
            ] as $card)<div class="rounded-xl border p-4 shadow-sm {{ $card[2] }}"><p class="text-xs font-semibold uppercase tracking-wide opacity-80">{{ $card[0] }}</p><p class="mt-2 text-2xl font-bold">{{ $card[1] }}</p></div>@endforeach
        </div>

        <form method="GET" action="{{ route('users.index') }}" class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:flex-row">
            <div class="relative flex-1"><svg class="absolute left-3 top-3 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><input name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o correo..." class="w-full rounded-lg border-slate-300 pl-10 text-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
            <select name="rol" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">Todos los roles</option><option value="admin" @selected(request('rol') === 'admin')>Administrador</option><option value="residente" @selected(request('rol') === 'residente')>Residente</option></select>
            <select name="estado" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">Todos los estados</option><option value="activo" @selected(request('estado') === 'activo')>Activo</option><option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivo</option></select>
            <button class="rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">Filtrar</button>
            @if(request()->hasAny(['buscar','rol','estado']))<a href="{{ route('users.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">Limpiar</a>@endif
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3.5">Usuario</th><th class="px-5 py-3.5">Rol</th><th class="px-5 py-3.5">Estado</th><th class="px-5 py-3.5">Registro</th><th class="px-5 py-3.5 text-right">Acciones</th></tr></thead>
                <tbody class="divide-y divide-slate-100">@forelse ($users as $user)<tr class="hover:bg-slate-50/70"><td class="px-5 py-4"><div class="flex items-center gap-3"><div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-bold uppercase text-indigo-700">{{ mb_substr($user->name, 0, 1) }}</div><div><p class="font-semibold text-slate-900">{{ $user->name }} @if(auth()->user()->is($user))<span class="ml-1 text-xs font-medium text-indigo-600">Tú</span>@endif</p><p class="text-xs text-slate-500">{{ $user->email }}</p></div></div></td><td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->rol === 'admin' ? 'bg-violet-100 text-violet-800' : 'bg-sky-100 text-sky-800' }}">{{ $user->rol === 'admin' ? 'Administrador' : 'Residente' }}</span></td><td class="px-5 py-4"><span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->activo ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-700' }}"><span class="h-1.5 w-1.5 rounded-full {{ $user->activo ? 'bg-green-500' : 'bg-slate-400' }}"></span>{{ $user->activo ? 'Activo' : 'Inactivo' }}</span></td><td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $user->created_at->format('d/m/Y') }}</td><td class="px-5 py-4"><div class="flex justify-end gap-3"><a href="{{ route('users.edit', $user) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Editar</a>@unless(auth()->user()->is($user))<form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('¿Eliminar este usuario y sus PQRS asociadas?')">@csrf @method('DELETE')<button class="font-semibold text-rose-600 hover:text-rose-800">Eliminar</button></form>@endunless</div></td></tr>@empty<tr><td colspan="5" class="px-5 py-14 text-center"><p class="font-medium text-slate-600">No hay usuarios registrados</p><p class="mt-1 text-sm text-slate-400">Ajusta los filtros o crea un usuario.</p></td></tr>@endforelse</tbody></table></div>
            @if($users->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $users->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
