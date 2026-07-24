@extends('layouts.app')
@section('titulo', 'Nueva solicitud')
@section('titulo_pagina', 'Nueva solicitud')
@section('contenido')
    <section class="page-heading compact"><div><a class="back-link" href="{{ route('pqrs.index') }}">← Volver al panel</a><h1>Radicar una nueva PQRS</h1><p>Registra la información necesaria para iniciar su seguimiento.</p></div></section>
    @include('pqrs.partials.form', ['action' => route('pqrs.store'), 'method' => 'POST', 'submitLabel' => 'Radicar solicitud'])
@endsection
