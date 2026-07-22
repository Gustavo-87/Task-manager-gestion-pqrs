@php
    $items = [
        ['label' => 'Panel principal', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6'],
        ['label' => Auth::user()->rol === 'admin' ? 'Gestión de PQRS' : 'Mis PQRS', 'route' => 'pqrs.index', 'active' => 'pqrs.*', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414A1 1 0 0117 7.414V19a2 2 0 01-2 2z'],
    ];
    if (Auth::user()->rol === 'admin') {
        $items = array_merge($items, [
            ['label' => 'Reportes', 'route' => 'reports.index', 'active' => 'reports.*', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414A1 1 0 0117 7.414V19a2 2 0 01-2 2z'],
            ['label' => 'Usuarios', 'route' => 'users.index', 'active' => 'users.*', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['label' => 'Configuración', 'route' => 'configuration.index', 'active' => 'configuration.*', 'also' => 'categories.*', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ['label' => 'Auditoría', 'route' => 'audits.index', 'active' => 'audits.*', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ]);
    }
@endphp

<div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden" x-cloak></div>
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-[18rem] max-w-[88vw] flex-col bg-slate-900 text-white transition-transform duration-200 sm:w-72 lg:translate-x-0">
    <div class="flex h-24 items-center gap-4 border-b border-slate-800 px-5 sm:px-6">
        <x-application-logo class="h-14 w-14 rounded-2xl bg-white p-1.5 shadow-none sm:h-16 sm:w-16" />
        <div><p class="text-base font-bold tracking-tight sm:text-lg">Gestión PQRS</p><p class="text-xs text-slate-400 sm:text-sm">Conjunto residencial</p></div>
        <button @click="sidebarOpen = false" class="ml-auto p-1 text-slate-400 lg:hidden" aria-label="Cerrar menú">✕</button>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5 sm:px-4">
        @foreach ($items as $item)
            @php $active = request()->routeIs($item['active']) || (isset($item['also']) && request()->routeIs($item['also'])); @endphp
            <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/></svg>
                {{ $item['label'] }}
            </a>
        @endforeach
        @if (Auth::user()->rol !== 'admin')
            <a href="{{ route('pqrs.create') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white">
                <span class="flex h-5 w-5 items-center justify-center text-xl">+</span> Nueva PQRS
            </a>
        @endif
    </nav>

    <div class="border-t border-slate-800 p-3">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Mi perfil</a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="mt-1 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Cerrar sesión</button>
        </form>
    </div>
</aside>
