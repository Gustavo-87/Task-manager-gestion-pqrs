@extends('layouts.app')

@section('titulo', 'Listado de PQRs')
@section('titulo_pagina', 'Listado de PQRs')

@section('contenido')
    <div class="row mb-3">
        <div class="col-md-4">
            <a href="{{ route('pqrs.create') }}" class="btn btn-primary">
                Nueva PQR
            </a>
        </div>

        <div class="col-md-8">
            <form method="GET" action="{{ route('pqrs.index') }}" class="row g-2">
                <div class="col-md-6">
                    <input
                        type="text"
                        name="buscar"
                        class="form-control"
                        placeholder="Buscar por asunto..."
                        value="{{ request('buscar') }}"
                    >
                </div>

                <div class="col-md-4">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="radicada" {{ request('estado') == 'radicada' ? 'selected' : '' }}>Radicada</option>
                        <option value="en_revision" {{ request('estado') == 'en_revision' ? 'selected' : '' }}>En revisión</option>
                        <option value="respondida" {{ request('estado') == 'respondida' ? 'selected' : '' }}>Respondida</option>
                        <option value="cerrada" {{ request('estado') == 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Asunto</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Fecha radicación</th>
                        <th>Fecha límite</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($pqrs as $pqr)
                        <tr>
                            <td>{{ $pqr->id }}</td>
                            <td>{{ $pqr->asunto }}</td>
                            <td>{{ $pqr->tipoPqr?->nombre ?? 'Sin tipo' }}</td>
                            <td>
                                @php
                                    $color = match($pqr->estado) {
                                        'respondida' => 'success',
                                        'cerrada' => 'secondary',
                                        'en_revision' => 'warning',
                                        default => 'primary',
                                    };
                                @endphp

                                <span class="badge bg-{{ $color }}">
                                    {{ str_replace('_', ' ', $pqr->estado) }}
                                </span>
                            </td>
                            <td>{{ $pqr->fecha_radicacion }}</td>
                            <td>{{ $pqr->fecha_limite_respuesta ?? 'No definida' }}</td>
                            <td>{{ $pqr->user?->name ?? 'Sin usuario' }}</td>
                            <td>
                                <a href="{{ route('pqrs.edit', $pqr->id) }}" class="btn btn-sm btn-warning">
                                    Editar
                                </a>

                                <form action="{{ route('pqrs.destroy', $pqr->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Eliminar esta PQR?')"
                                    >
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No hay PQRs registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection