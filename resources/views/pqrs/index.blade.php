@extends('layouts.app')

@section('titulo', 'Panel general')
@section('titulo_pagina', 'Panel general')

@section('contenido')
    <section class="page-heading">
        <div>
            <span class="eyebrow">Resumen operativo</span>
            <h1>Gestiona cada solicitud a tiempo</h1>
            <p>{{ auth()->user()->role === 'gestor' ? 'Consulta el estado de las PQRS y prioriza las que requieren atención.' : 'Consulta el estado y seguimiento de tus solicitudes.' }}</p>
        </div>
        <div class="heading-actions"><a href="{{ route('reports.xlsx', request()->query()) }}" class="button subtle">↓ Excel</a><a href="{{ route('reports.pdf', request()->query()) }}" class="button subtle">↓ PDF</a><a href="{{ route('pqrs.create') }}" class="button primary"><span>＋</span> Radicar solicitud</a></div>
    </section>

    <section class="metrics" aria-label="Resumen de solicitudes">
        <a class="metric-card {{ !request('estado') ? 'selected' : '' }}" href="{{ route('pqrs.index') }}#solicitudes"><span class="metric-icon mint">▤</span><div><small>Total de solicitudes</small><strong>{{ $resumen['total'] }}</strong><span class="metric-note">Ver todos los registros</span></div></a>
        <a class="metric-card {{ request('estado') === 'pendientes' ? 'selected' : '' }}" href="{{ route('pqrs.index', ['estado' => 'pendientes']) }}#solicitudes"><span class="metric-icon amber">◷</span><div><small>Pendientes</small><strong>{{ $resumen['pendientes'] }}</strong><span class="metric-note">Requieren gestión</span></div></a>
        <a class="metric-card {{ request('estado') === 'por_vencer' ? 'selected' : '' }}" href="{{ route('pqrs.index', ['estado' => 'por_vencer']) }}#solicitudes"><span class="metric-icon coral">!</span><div><small>Por vencer</small><strong>{{ $resumen['por_vencer'] }}</strong><span class="metric-note">Próximos 3 días</span></div></a>
        <a class="metric-card {{ request('estado') === 'respondida' ? 'selected' : '' }}" href="{{ route('pqrs.index', ['estado' => 'respondida']) }}#solicitudes"><span class="metric-icon blue">✓</span><div><small>Respondidas</small><strong>{{ $resumen['respondidas'] }}</strong><span class="metric-note">Gestión completada</span></div></a>
    </section>

    @php
        $stateTotal = max(1, collect($chartData['states'])->sum());
        $maxMonth = max(1, $chartData['months']->max('value'));
        $respondedPercent = round((($chartData['states']['respondida'] ?? 0) + ($chartData['states']['cerrada'] ?? 0)) / $stateTotal * 100);
    @endphp
    <section class="insights-grid" aria-label="Indicadores visuales">
        <article class="chart-card"><div class="chart-heading"><div><span class="eyebrow">Tendencia</span><h2>Solicitudes por mes</h2></div><span class="chart-badge">Últimos 6 meses</span></div><div class="bar-chart">@foreach($chartData['months'] as $month)<div class="bar-item"><span class="bar-value">{{ $month['value'] }}</span><div class="bar-track"><i style="height: {{ max(8, round($month['value'] / $maxMonth * 100)) }}%"></i></div><small>{{ $month['label'] }}</small></div>@endforeach</div></article>
        <article class="chart-card progress-card"><div class="chart-heading"><div><span class="eyebrow">Cumplimiento</span><h2>Gestión completada</h2></div></div><div class="donut" style="--progress: {{ $respondedPercent * 3.6 }}deg"><span><strong>{{ $respondedPercent }}%</strong><small>resueltas</small></span></div><div class="chart-legend"><span><i class="green"></i>Respondidas o cerradas</span><span><i></i>En proceso</span></div></article>
    </section>

    <section class="panel" id="solicitudes">
        <div class="panel-header">
            <div><h2>Solicitudes recientes</h2><p>Todos los casos ordenados por fecha de radicación.</p></div>
            <form method="GET" action="{{ route('pqrs.index') }}" class="filters">
                <label class="search-box"><span>⌕</span><input type="search" name="buscar" placeholder="Radicado, asunto, residente..." value="{{ request('buscar') }}" aria-label="Buscar solicitudes"></label>
                <select name="estado" aria-label="Filtrar por estado" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="pendientes" @selected(request('estado') === 'pendientes')>Pendientes</option>
                    <option value="por_vencer" @selected(request('estado') === 'por_vencer')>Por vencer</option>
                    <option value="radicada" @selected(request('estado') === 'radicada')>Radicada</option>
                    <option value="en_revision" @selected(request('estado') === 'en_revision')>En revisión</option>
                    <option value="respondida" @selected(request('estado') === 'respondida')>Respondida</option>
                    <option value="cerrada" @selected(request('estado') === 'cerrada')>Cerrada</option>
                </select>
                <select name="tipo_pqr_id" aria-label="Filtrar por tipo"><option value="">Todos los tipos</option>@foreach($tipos as $tipo)<option value="{{ $tipo->id }}" @selected(request('tipo_pqr_id') == $tipo->id)>{{ $tipo->nombre }}</option>@endforeach</select>
                @if(auth()->user()->canViewAllPqrs())<select name="assigned_to_id" aria-label="Filtrar por responsable"><option value="">Todos los responsables</option>@foreach($gestores as $gestor)<option value="{{ $gestor->id }}" @selected(request('assigned_to_id') == $gestor->id)>{{ $gestor->name }}</option>@endforeach</select>@endif
                <label class="date-filter">Desde<input type="date" name="desde" value="{{ request('desde') }}"></label><label class="date-filter">Hasta<input type="date" name="hasta" value="{{ request('hasta') }}"></label>
                <button class="button subtle" type="submit">Filtrar</button>
                @if(request()->hasAny(['buscar', 'estado', 'tipo_pqr_id', 'assigned_to_id', 'desde', 'hasta']))<a class="clear-filter" href="{{ route('pqrs.index') }}">Limpiar</a>@endif
            </form>
        </div>
        <div class="mobile-request-list">
            @forelse($pqrs as $pqr)
                <article class="request-card"><a class="request-card-link" href="{{ route('pqrs.show', $pqr) }}"><div class="request-card-top"><span class="radicado">PQR-{{ str_pad($pqr->id, 4, '0', STR_PAD_LEFT) }}</span><span class="status {{ $pqr->estado }}"><i></i>{{ $pqr->estado_label }}</span></div><strong>{{ $pqr->asunto }}</strong><p>{{ Str::limit($pqr->descripcion, 75) }}</p><div class="request-meta"><span>{{ $pqr->tipoPqr?->nombre ?? 'Sin tipo' }}</span><span>Vence {{ $pqr->fecha_limite_respuesta?->format('d M') ?? 'sin fecha' }}</span></div></a>@can('update',$pqr)<form class="mobile-quick-action" method="POST" action="{{ route('pqrs.quick-update',$pqr) }}" data-confirm="Actualizar solicitud" data-confirm-message="El cambio quedará registrado en el historial.">@csrf @method('PATCH')<select name="estado" aria-label="Estado"><option value="radicada" @selected($pqr->estado==='radicada')>Radicada</option><option value="en_revision" @selected($pqr->estado==='en_revision')>En revisión</option><option value="respondida" @selected($pqr->estado==='respondida')>Respondida</option><option value="cerrada" @selected($pqr->estado==='cerrada')>Cerrada</option></select><select name="assigned_to_id" aria-label="Responsable"><option value="">Sin asignar</option>@foreach($gestores as $gestor)<option value="{{ $gestor->id }}" @selected($pqr->assigned_to_id===$gestor->id)>{{ $gestor->name }}</option>@endforeach</select><button>Aplicar</button></form>@endcan</article>
            @empty
                <div class="empty-state mobile-empty"><span class="empty-illustration"><i></i><b>✓</b></span><h3>Todo está despejado</h3><p>No encontramos solicitudes con estos filtros.</p><a href="{{ route('pqrs.create') }}" class="button primary">Radicar solicitud</a></div>
            @endforelse
        </div>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Radicado</th><th>Solicitud</th><th>Tipo</th><th>Estado</th><th>Fecha límite</th><th>Responsable</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                @forelse ($pqrs as $pqr)
                    <tr>
                        <td><span class="radicado">PQR-{{ str_pad($pqr->id, 4, '0', STR_PAD_LEFT) }}</span><small>{{ $pqr->fecha_radicacion->format('d/m/Y') }}</small></td>
                        <td><a class="subject-link" href="{{ route('pqrs.show', $pqr) }}"><strong class="subject">{{ $pqr->asunto }}</strong><small>{{ Str::limit($pqr->descripcion, 48) }}</small></a></td>
                        <td><span class="type-label">{{ $pqr->tipoPqr?->nombre ?? 'Sin tipo' }}</span></td>
                        <td><span class="status {{ $pqr->estado }}"><i></i>{{ $pqr->estado_label }}</span></td>
                        <td><span class="deadline {{ $pqr->is_overdue ? 'overdue' : '' }}">{{ $pqr->fecha_limite_respuesta?->format('d M Y') ?? 'Sin definir' }}</span>@if($pqr->is_overdue)<small class="danger-text">Plazo vencido</small>@endif</td>
                        <td><span class="owner"><span class="avatar small">{{ Str::upper(Str::substr($pqr->assignee?->name ?? 'SA', 0, 2)) }}</span>{{ $pqr->assignee?->name ?? 'Sin asignar' }}</span></td>
                        <td class="actions">
                            @can('update', $pqr)<details class="quick-menu"><summary class="icon-button" aria-label="Acciones para {{ $pqr->asunto }}">•••</summary><div><strong>Acción rápida</strong><form method="POST" action="{{ route('pqrs.quick-update',$pqr) }}" data-confirm="Actualizar solicitud" data-confirm-message="Se registrará este cambio en el historial y se notificará al residente si cambia el estado.">@csrf @method('PATCH')<select name="estado"><option value="radicada" @selected($pqr->estado==='radicada')>Radicada</option><option value="en_revision" @selected($pqr->estado==='en_revision')>En revisión</option><option value="respondida" @selected($pqr->estado==='respondida')>Respondida</option><option value="cerrada" @selected($pqr->estado==='cerrada')>Cerrada</option></select><select name="assigned_to_id"><option value="">Sin asignar</option>@foreach($gestores as $gestor)<option value="{{ $gestor->id }}" @selected($pqr->assigned_to_id===$gestor->id)>{{ $gestor->name }}</option>@endforeach</select><button class="button primary">Aplicar</button><a href="{{ route('pqrs.edit',$pqr) }}">Edición completa</a></form></div></details>@else<a href="{{ route('pqrs.show', $pqr) }}" class="icon-button" aria-label="Ver {{ $pqr->asunto }}">→</a>@endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state"><span class="empty-illustration"><i></i><b>✓</b></span><h3>Todo está despejado</h3><p>No encontramos solicitudes con estos filtros. Prueba otra búsqueda o registra una nueva PQRS.</p><a href="{{ route('pqrs.create') }}" class="button primary">Radicar solicitud</a></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-footer"><span>Mostrando {{ $pqrs->firstItem() ?? 0 }}–{{ $pqrs->lastItem() ?? 0 }} de {{ $pqrs->total() }} solicitudes</span><nav class="simple-pagination" aria-label="Paginación">@if($pqrs->onFirstPage())<span>← Anterior</span>@else<a href="{{ $pqrs->previousPageUrl() }}">← Anterior</a>@endif<strong>Página {{ $pqrs->currentPage() }} de {{ $pqrs->lastPage() }}</strong>@if($pqrs->hasMorePages())<a href="{{ $pqrs->nextPageUrl() }}">Siguiente →</a>@else<span>Siguiente →</span>@endif</nav></div>
    </section>
@endsection
