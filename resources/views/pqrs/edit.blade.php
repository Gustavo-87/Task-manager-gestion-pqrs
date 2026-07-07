@extends('layouts.app')

@section('titulo', 'Editar PQR')
@section('titulo_pagina', 'Editar PQR')

@section('contenido')
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('pqrs.update', $pqr->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="asunto" class="form-label">Asunto <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        class="form-control @error('asunto') is-invalid @enderror"
                        id="asunto"
                        name="asunto"
                        value="{{ old('asunto', $pqr->asunto) }}"
                    >
                    @error('asunto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                    <textarea
                        class="form-control @error('descripcion') is-invalid @enderror"
                        id="descripcion"
                        name="descripcion"
                        rows="4"
                    >{{ old('descripcion', $pqr->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tipo_pqr_id" class="form-label">Tipo de PQR <span class="text-danger">*</span></label>
                    <select
                        class="form-select @error('tipo_pqr_id') is-invalid @enderror"
                        id="tipo_pqr_id"
                        name="tipo_pqr_id"
                    >
                        <option value="">Seleccione...</option>
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo->id }}" {{ old('tipo_pqr_id', $pqr->tipo_pqr_id) == $tipo->id ? 'selected' : '' }}>
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_pqr_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                    <select
                        class="form-select @error('estado') is-invalid @enderror"
                        id="estado"
                        name="estado"
                    >
                        <option value="radicada" {{ old('estado', $pqr->estado) == 'radicada' ? 'selected' : '' }}>Radicada</option>
                        <option value="en_revision" {{ old('estado', $pqr->estado) == 'en_revision' ? 'selected' : '' }}>En revisión</option>
                        <option value="respondida" {{ old('estado', $pqr->estado) == 'respondida' ? 'selected' : '' }}>Respondida</option>
                        <option value="cerrada" {{ old('estado', $pqr->estado) == 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                    </select>
                    @error('estado')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="fecha_radicacion" class="form-label">Fecha de radicación <span class="text-danger">*</span></label>
                    <input
                        type="date"
                        class="form-control @error('fecha_radicacion') is-invalid @enderror"
                        id="fecha_radicacion"
                        name="fecha_radicacion"
                        value="{{ old('fecha_radicacion', $pqr->fecha_radicacion) }}"
                    >
                    @error('fecha_radicacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="fecha_limite_respuesta" class="form-label">Fecha límite de respuesta</label>
                    <input
                        type="date"
                        class="form-control @error('fecha_limite_respuesta') is-invalid @enderror"
                        id="fecha_limite_respuesta"
                        name="fecha_limite_respuesta"
                        value="{{ old('fecha_limite_respuesta', $pqr->fecha_limite_respuesta) }}"
                    >
                    @error('fecha_limite_respuesta')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Actualizar PQR</button>
                <a href="{{ route('pqrs.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection