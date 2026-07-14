<!DOCTYPE html>
<html lang="es"><body style="margin:0;background:#f3f4f6;font-family:Arial,sans-serif;color:#1f2937">
<div style="max-width:620px;margin:32px auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
    <div style="background:#1e3a5f;padding:24px;color:#fff"><h1 style="margin:0;font-size:20px">{{ $settings->residential_name }} · Gestión de PQR</h1></div>
    <div style="padding:28px">
        <p>Hola, {{ $recipient->name }}.</p>
        <p>El estado de tu PQR ha sido actualizado.</p>
        <table style="width:100%;border-collapse:collapse;margin:20px 0">
            <tr><td style="padding:9px;border-bottom:1px solid #e5e7eb"><strong>Radicado</strong></td><td style="padding:9px;border-bottom:1px solid #e5e7eb">#{{ $pqr->id }}</td></tr>
            <tr><td style="padding:9px;border-bottom:1px solid #e5e7eb"><strong>Asunto</strong></td><td style="padding:9px;border-bottom:1px solid #e5e7eb">{{ $pqr->asunto }}</td></tr>
            <tr><td style="padding:9px;border-bottom:1px solid #e5e7eb"><strong>Estado anterior</strong></td><td style="padding:9px;border-bottom:1px solid #e5e7eb">{{ $previousStatus }}</td></tr>
            <tr><td style="padding:9px"><strong>Estado nuevo</strong></td><td style="padding:9px">{{ $newStatus }}</td></tr>
        </table>
        @if($pqr->respuesta && $pqr->estado === 'respondida')
            <div style="margin:20px 0;padding:16px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px">
                <strong>Respuesta</strong>
                <p style="margin:10px 0 0;white-space:pre-line">{{ $pqr->respuesta }}</p>
            </div>
        @endif
        <p style="color:#6b7280;font-size:13px">Este es un mensaje automático. No respondas a este correo.</p>
    </div>
</div></body></html>
