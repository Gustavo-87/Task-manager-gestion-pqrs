@extends('layouts.app')
@section('titulo', 'Configuración')
@section('titulo_pagina', 'Configuración del conjunto')
@section('contenido')
    <section class="page-heading compact"><div><span class="eyebrow">Administración</span><h1>Identidad del conjunto</h1><p>Estos datos aparecerán en la plataforma y podrán adaptarse para cada propiedad residencial.</p></div></section>
    <form method="POST" action="{{ route('settings.update') }}" class="form-panel settings-panel" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="form-intro"><span class="form-step">01</span><div><h2>Información institucional</h2><p>Configura el nombre visible, la información legal y los canales de contacto.</p></div></div>
        @if($errors->any())<div class="notice error" role="alert"><span>!</span><div><strong>Revisa la información ingresada.</strong><p>Hay campos que necesitan tu atención.</p></div></div>@endif
        <div class="form-grid">
            <div class="field span-2 brand-upload"><label for="logo">Logo institucional</label><div class="brand-preview-card"><div class="brand-preview" id="brandPreview"><img src="{{ $settings->logo_path ? Storage::url($settings->logo_path) : asset('logo-resuelve.png') }}" alt="Vista previa del logo"></div><div><strong>Imagen de la propiedad</strong><p>PNG, JPG o WEBP de hasta 2 MB. Se recomienda una imagen cuadrada.</p><label for="logo" class="button subtle">Seleccionar imagen</label><input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only">@error('logo')<small class="field-error">{{ $message }}</small>@enderror</div></div></div>
            <div class="field span-2"><label for="nombre_conjunto">Nombre del conjunto *</label><input id="nombre_conjunto" name="nombre_conjunto" value="{{ old('nombre_conjunto', $settings->nombre_conjunto) }}" required placeholder="Ej. Conjunto Residencial Los Robles">@error('nombre_conjunto')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="nit">NIT</label><input id="nit" name="nit" value="{{ old('nit', $settings->nit) }}" placeholder="900.000.000-0"></div>
            <div class="field"><label for="representante_legal">Representante legal</label><input id="representante_legal" name="representante_legal" value="{{ old('representante_legal', $settings->representante_legal) }}"></div>
            <div class="field"><label for="direccion">Dirección</label><input id="direccion" name="direccion" value="{{ old('direccion', $settings->direccion) }}"></div>
            <div class="field"><label for="ciudad">Ciudad</label><input id="ciudad" name="ciudad" value="{{ old('ciudad', $settings->ciudad) }}"></div>
            <div class="field"><label for="telefono">Teléfono</label><input id="telefono" name="telefono" value="{{ old('telefono', $settings->telefono) }}"></div>
            <div class="field"><label for="email">Correo institucional</label><input id="email" name="email" type="email" value="{{ old('email', $settings->email) }}"></div>
            <div class="field"><label for="dias_respuesta">Plazo estándar de respuesta</label><div class="input-suffix"><input id="dias_respuesta" name="dias_respuesta" type="number" min="1" max="120" value="{{ old('dias_respuesta', $settings->dias_respuesta) }}" required><span>días</span></div></div>
            <div class="field"><label for="color_principal">Color institucional</label><div class="color-input"><input id="color_principal_picker" type="color" value="{{ old('color_principal', $settings->color_principal) }}"><input id="color_principal" name="color_principal" value="{{ old('color_principal', $settings->color_principal) }}" pattern="#[0-9a-fA-F]{6}" required></div><small class="field-help">La vista previa se aplica mientras eliges el color.</small></div>
        </div>
        <div class="form-actions"><div><a href="{{ route('pqrs.index') }}" class="button ghost">Cancelar</a><button class="button primary" type="submit">Guardar configuración <span>→</span></button></div></div>
    </form>
@endsection
