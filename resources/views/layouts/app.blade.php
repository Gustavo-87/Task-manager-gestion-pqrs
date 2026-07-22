<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Gestión de PQRS') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden bg-slate-50 font-sans text-slate-800 antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen">
            @include('layouts.navigation')

            <div class="lg:pl-80">
                <header class="sticky top-0 z-20 flex min-h-16 flex-wrap items-center gap-3 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:px-6 lg:px-8">
                    <button @click="sidebarOpen = true" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Abrir menú">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="hidden text-sm text-slate-500 sm:block">Sistema de gestión para conjuntos residenciales</div>
                    <div class="ml-auto flex items-center gap-3 sm:gap-4">
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                            <p class="text-xs capitalize text-slate-500">{{ Auth::user()->rol }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold uppercase text-indigo-700" title="Mi perfil">
                            {{ mb_substr(Auth::user()->name, 0, 1) }}
                        </a>
                    </div>
                </header>

                @isset($header)
                    <header class="border-b border-slate-200 bg-white px-4 py-5 sm:px-6 lg:px-8">
                        {{ $header }}
                    </header>
                @endisset

                <main class="overflow-x-hidden">{{ $slot }}</main>
            </div>
        </div>
    </body>
</html>
