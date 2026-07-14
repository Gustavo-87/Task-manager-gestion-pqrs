<!DOCTYPE html>
<html lang="es"><body style="margin:0;background:#f3f4f6;font-family:Arial,sans-serif;color:#1f2937">
<div style="max-width:620px;margin:32px auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
    <div style="background:#b45309;padding:24px;color:#fff"><h1 style="margin:0;font-size:20px">{{ $settings->residential_name }} · Alerta de vencimiento</h1></div>
    <div style="padding:28px">
        <p>Hola, {{ $recipient->name }}.</p>
        <p>La PQR <strong>#{{ $pqr->id }} {{ $timing }}</strong> y todavía requiere gestión.</p>
        <table style="width:100%;border-collapse:collapse;margin:20px 0">
            <tr><td style="padding:9px;border-bottom:1px solid #e5e7eb"><strong>Asunto</strong></td><td style="padding:9px;border-bottom:1px solid #e5e7eb">{{ $pqr->asunto }}</td></tr>
            <tr><td style="padding:9px;border-bottom:1px solid #e5e7eb"><strong>Residente</strong></td><td style="padding:9px;border-bottom:1px solid #e5e7eb">{{ $pqr->user?->name ?? 'Sin residente' }}</td></tr>
            <tr><td style="padding:9px"><strong>Fecha límite</strong></td><td style="padding:9px">{{ $pqr->fecha_limite_respuesta->format('d/m/Y') }}</td></tr>
        </table>
        <p style="color:#6b7280;font-size:13px">Este es un mensaje automático del sistema de gestión de PQR.</p>
    </div>
</div></body></html>
