<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 22px 26px; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 9px; }
        .header { border-bottom: 3px solid #1e3a5f; padding-bottom: 12px; margin-bottom: 16px; }
        .logo { width: 72px; height: 52px; object-fit: contain; border-radius: 7px; vertical-align: middle; }
        .title { display: inline-block; vertical-align: middle; margin-left: 16px; }
        h1 { margin: 0 0 4px; color: #1e3a5f; font-size: 20px; }
        h2 { color: #1e3a5f; font-size: 13px; margin: 18px 0 7px; }
        .muted { color: #6b7280; }
        .cards { width: 100%; border-collapse: separate; border-spacing: 6px; margin-left: -6px; }
        .card { padding: 10px; border: 1px solid #dbe4ee; background: #f8fafc; border-radius: 6px; }
        .card strong { display: block; font-size: 17px; color: #1e3a5f; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #1e3a5f; color: white; text-align: left; padding: 6px; font-size: 8px; }
        table.data td { border-bottom: 1px solid #e5e7eb; padding: 6px; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
        .grid { width: 100%; }
        .grid td { width: 50%; vertical-align: top; padding-right: 15px; }
        .badge { padding: 2px 5px; border-radius: 8px; background: #e5e7eb; white-space: nowrap; }
        .empty { padding: 12px; background: #f8fafc; color: #6b7280; }
        .footer { margin-top: 18px; padding-top: 8px; border-top: 1px solid #d1d5db; color: #6b7280; font-size: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <img class="logo" src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('images/logo-gestion-pqrs.jpg'))) }}" alt="Logo">
        <div class="title">
            <h1>{{ $settings->residential_name }} · Reporte de gestión de PQRS</h1>
            @if ($settings->nit || $settings->address)<div class="muted">{{ $settings->nit ? 'NIT '.$settings->nit : '' }}{{ $settings->nit && $settings->address ? ' · ' : '' }}{{ $settings->address }}</div>@endif
            <div class="muted">Periodo: {{ $dateFrom->format('d/m/Y') }} al {{ $dateTo->format('d/m/Y') }}</div>
            <div class="muted">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <table class="cards"><tr>
        <td class="card"><strong>{{ $pqrs->count() }}</strong>Total</td>
        <td class="card"><strong>{{ $byStatus['radicada'] }}</strong>Radicadas</td>
        <td class="card"><strong>{{ $byStatus['en_revision'] }}</strong>En revisión</td>
        <td class="card"><strong>{{ $byStatus['en_proceso'] }}</strong>En proceso</td>
        <td class="card"><strong>{{ $byStatus['en_espera'] }}</strong>En espera</td>
        <td class="card"><strong>{{ $byStatus['rechazada'] }}</strong>Rechazadas</td>
        <td class="card"><strong>{{ $byStatus['resuelta'] }}</strong>Resueltas</td>
        <td class="card"><strong>{{ $byStatus['cerrada'] }}</strong>Cerradas</td>
        <td class="card"><strong>{{ $overdue->count() }}</strong>Vencidas</td>
    </tr></table>

    <table class="grid"><tr><td>
        <h2>Resumen por categoría</h2>
        <table class="data"><thead><tr><th>Categoría</th><th>Total</th></tr></thead><tbody>
        @forelse ($byCategory as $category => $total)<tr><td>{{ $category }}</td><td>{{ $total }}</td></tr>@empty<tr><td colspan="2">Sin datos</td></tr>@endforelse
        </tbody></table>
    </td><td>
        <h2>Alertas de vencimiento</h2>
        <table class="data"><thead><tr><th>Condición</th><th>Total</th></tr></thead><tbody>
            <tr><td>Vencidas</td><td>{{ $overdue->count() }}</td></tr>
            <tr><td>Vencen mañana</td><td>{{ $upcoming->count() }}</td></tr>
        </tbody></table>
    </td></tr></table>

    <h2>Detalle de PQRS</h2>
    <table class="data">
        <thead><tr><th>#</th><th>Radicación</th><th>Límite</th><th>Asunto</th><th>Categoría</th><th>Residente</th><th>Estado</th></tr></thead>
        <tbody>
        @forelse ($pqrs as $pqr)
            <tr><td>{{ $pqr->id }}</td><td>{{ $pqr->fecha_radicacion->format('d/m/Y') }}</td><td>{{ $pqr->fecha_limite_respuesta->format('d/m/Y') }}</td><td>{{ $pqr->asunto }}</td><td>{{ $pqr->tipoPqr?->nombre ?? 'Sin categoría' }}</td><td>{{ $pqr->user?->name ?? 'Sin usuario' }}</td><td><span class="badge">{{ \App\Models\Pqr::statusLabel($pqr->estado) }}</span></td></tr>
        @empty
            <tr><td colspan="7" class="empty">No hay PQRS radicadas en el periodo seleccionado.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>PQRS vencidas</h2>
    <table class="data"><thead><tr><th>#</th><th>Asunto</th><th>Fecha límite</th><th>Residente</th></tr></thead><tbody>
    @forelse ($overdue as $pqr)<tr><td>{{ $pqr->id }}</td><td>{{ $pqr->asunto }}</td><td>{{ $pqr->fecha_limite_respuesta->format('d/m/Y') }}</td><td>{{ $pqr->user?->name ?? 'Sin usuario' }}</td></tr>@empty<tr><td colspan="4" class="empty">No hay PQRS vencidas en el periodo.</td></tr>@endforelse
    </tbody></table>

    <h2>PQRS próximas a vencer</h2>
    <table class="data"><thead><tr><th>#</th><th>Asunto</th><th>Fecha límite</th><th>Residente</th></tr></thead><tbody>
    @forelse ($upcoming as $pqr)<tr><td>{{ $pqr->id }}</td><td>{{ $pqr->asunto }}</td><td>{{ $pqr->fecha_limite_respuesta->format('d/m/Y') }}</td><td>{{ $pqr->user?->name ?? 'Sin usuario' }}</td></tr>@empty<tr><td colspan="4" class="empty">No hay PQRS que venzan mañana.</td></tr>@endforelse
    </tbody></table>

    <div class="footer">Documento generado por el sistema académico de Gestión de PQRS.</div>
</body>
</html>
