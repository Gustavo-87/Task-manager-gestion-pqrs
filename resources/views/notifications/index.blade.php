@extends('layouts.app')
@section('titulo', 'Notificaciones')
@section('titulo_pagina', 'Notificaciones')
@section('contenido')
<section class="page-heading"><div><span class="eyebrow">Centro de avisos</span><h1>Mantente al día</h1><p>Cambios, asignaciones y respuestas de tus solicitudes.</p></div>@if(auth()->user()->unreadNotifications()->count())<form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="button subtle">Marcar todas como leídas</button></form>@endif</section>
<section class="panel notification-list">@forelse($notifications as $notification)<a href="{{ route('notifications.read', $notification) }}" class="notification-row {{ $notification->read_at ? '' : 'unread' }}"><span class="notification-dot"></span><div><strong>{{ $notification->data['title'] ?? 'Notificación' }}</strong><p>{{ $notification->data['message'] ?? '' }}</p><small>{{ $notification->created_at->diffForHumans() }}</small></div><span class="notification-arrow">→</span></a>@empty<div class="empty-state"><span class="empty-illustration"><i></i><b>✓</b></span><h3>Estás al día</h3><p>No tienes notificaciones pendientes.</p></div>@endforelse<div class="panel-footer">{{ $notifications->links() }}</div></section>
@endsection
