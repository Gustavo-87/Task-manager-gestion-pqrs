<!DOCTYPE html>
@php($brandLogo = $siteSettings->logo_path ? Storage::url($siteSettings->logo_path) : asset('logo-resuelve.png'))
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#12382f">
    <meta name="description" content="Plataforma para la gestión y seguimiento de peticiones, quejas, reclamos y sugerencias.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $siteSettings->nombre_conjunto }} · Gestión de PQRS">
    <meta property="og:description" content="Gestión de PQRS clara y a tiempo.">
    <meta property="og:image" content="{{ url('/og.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $siteSettings->nombre_conjunto }} · Gestión de PQRS">
    <meta name="twitter:description" content="Gestión de PQRS clara y a tiempo.">
    <meta name="twitter:image" content="{{ url('/og.png') }}">
    <link rel="icon" href="{{ $brandLogo }}?v={{ $siteSettings->updated_at?->timestamp ?? 1 }}">
    <link rel="apple-touch-icon" href="{{ $brandLogo }}?v={{ $siteSettings->updated_at?->timestamp ?? 1 }}">
    <title>@yield('titulo', 'Panel') · {{ $siteSettings->nombre_conjunto }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="--forest: {{ $siteSettings->color_principal }}">
    <div class="app-shell">
        <aside class="sidebar app-navbar" id="sidebar">
            <a class="brand" href="{{ route('pqrs.index') }}" aria-label="Inicio de Resuelve">
                <span class="brand-mark image-mark"><img src="{{ $brandLogo }}" alt=""></span>
                <span><strong>Resuelve</strong><small>{{ $siteSettings->nombre_conjunto }}</small></span>
            </a>
            <nav class="main-nav" aria-label="Navegación principal">
                <a class="nav-item {{ request()->routeIs('pqrs.index') ? 'active' : '' }}" href="{{ route('pqrs.index') }}"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M3 11 12 4l9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1Z"/></svg><span>Panel general</span></a>
                <a class="nav-item {{ request()->routeIs('pqrs.show', 'pqrs.edit') ? 'active' : '' }}" href="{{ route('pqrs.index') }}#solicitudes"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M6 4h12v16H6zM9 8h6M9 12h6M9 16h4"/></svg><span>Solicitudes</span></a>
                <a class="nav-item {{ request()->routeIs('pqrs.create') ? 'active' : '' }}" href="{{ route('pqrs.create') }}"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg><span>Nueva solicitud</span></a>
                <a class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"/></svg><span>Notificaciones</span>@if(auth()->user()->unreadNotifications()->count())<b class="nav-badge">{{ auth()->user()->unreadNotifications()->count() }}</b>@endif</a>
                <a class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><svg class="nav-icon" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c1-5 4-7 8-7s7 2 8 7"/></svg><span>Mi perfil</span></a>
                @if(in_array(auth()->user()->role, ['admin','gestor']))
                    <details class="nav-config" @if(request()->routeIs('settings.*', 'users.*', 'management.workload')) open @endif>
                        <summary class="nav-item {{ request()->routeIs('settings.*', 'users.*', 'management.workload') ? 'active' : '' }}"><svg class="nav-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1l2-1.5-2-3.4-2.4 1A8 8 0 0 0 15 6l-.3-2.5h-4L10.4 6a8 8 0 0 0-1.6 1L6.5 6 4.5 9.5l2 1.5a7 7 0 0 0 0 2l-2 1.5 2 3.4 2.3-1a8 8 0 0 0 1.6 1l.3 2.6h4L15 18a8 8 0 0 0 1.5-1l2.4 1 2-3.5-2-1.5a7 7 0 0 0 .1-1Z"/></svg><span>Configuración</span><span class="nav-chevron">⌄</span></summary>
                        <div class="nav-submenu">
                            <a href="{{ route('settings.edit') }}">Configuración general</a>
                            @if(auth()->user()->isAdmin())<a href="{{ route('users.index') }}">Usuarios y roles</a>@endif
                            <a href="{{ route('management.workload') }}">Carga del equipo</a>
                        </div>
                    </details>
                @endif
                @if(in_array(auth()->user()->role,['admin','gestor']))<a class="nav-item {{ request()->routeIs('management.tools') ? 'active' : '' }}" href="{{ route('management.tools') }}"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10M10 18h4"/></svg><span>Herramientas</span></a>@endif
                @if(auth()->user()->isAdmin())<a class="nav-item {{ request()->routeIs('management.residents') ? 'active' : '' }}" href="{{ route('management.residents') }}"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M4 21V8l8-5 8 5v13M8 11h2M14 11h2M8 15h2M14 15h2"/></svg><span>Residentes</span></a><a class="nav-item {{ request()->routeIs('management.audit') ? 'active' : '' }}" href="{{ route('management.audit') }}"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 3 4 6v6c0 5 3 8 8 9 5-1 8-4 8-9V6Z"/></svg><span>Auditoría</span></a>@endif
            </nav>
            <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Contraer menú">‹</button>
            <div class="sidebar-help navbar-help">
                <span class="help-icon condominium-logo"><img src="{{ $brandLogo }}" alt="Logo de {{ $siteSettings->nombre_conjunto }}"></span>
                <strong>{{ $siteSettings->nombre_conjunto }}</strong>
                <p>@if($siteSettings->nit)NIT {{ $siteSettings->nit }}<br>@endif{{ $siteSettings->direccion }}@if($siteSettings->direccion && $siteSettings->ciudad), @endif{{ $siteSettings->ciudad }}@if($siteSettings->email)<br>{{ $siteSettings->email }}@endif</p>
            </div>
            <div class="sidebar-user">
                <span class="avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 2)) }}</span>
                <span><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(auth()->user()->role) }}</small></span>
                <form method="POST" action="{{ route('logout') }}" class="logout-form">@csrf<button type="submit" title="Cerrar sesión" aria-label="Cerrar sesión">↪</button></form>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <button class="menu-button" id="menuButton" type="button" aria-label="Abrir menú" aria-controls="sidebar" aria-expanded="false">☰</button>
                <span class="topbar-logo" aria-hidden="true"><img src="{{ $brandLogo }}" alt=""></span>
                <div class="topbar-title"><span class="eyebrow">{{ $siteSettings->nombre_conjunto }}</span><strong>@yield('titulo_pagina', 'Panel general')</strong></div>
                <div class="topbar-actions"><span class="today">{{ now()->translatedFormat('d M Y') }}</span><a class="notification-button" href="{{ route('notifications.index') }}" aria-label="Notificaciones"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 6-3 7-3 9h18c0-2-3-3-3-9ZM10 21h4"/></svg>@if(auth()->user()->unreadNotifications()->count())<b>{{ auth()->user()->unreadNotifications()->count() }}</b>@endif</a><button class="theme-toggle" id="themeToggle" type="button" aria-label="Activar modo oscuro"><span class="sun">☀</span><span class="moon">☾</span></button></div>
            </header>

            <div class="page-content">
                @if(session('success'))
                    <div class="notice success" role="status">
                        <span>✓</span>
                        {{ session('success') }}
                    </div>
                @endif

                @yield('contenido')
            </div>
            </main>
    </div>
    <dialog class="confirm-dialog" id="confirmDialog"><div><span class="confirm-icon">!</span><h2 id="confirmTitle">Confirmar acción</h2><p id="confirmMessage">¿Deseas continuar?</p><div><button class="button ghost" id="confirmCancel" type="button">Cancelar</button><button class="button danger" id="confirmAccept" type="button">Confirmar</button></div></div></dialog>
</body>
</html>
