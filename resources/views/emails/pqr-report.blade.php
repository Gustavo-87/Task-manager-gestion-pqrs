<!DOCTYPE html>
<html lang="es"><body style="margin:0;background:#f3f4f6;font-family:Arial,sans-serif;color:#1f2937">
<div style="max-width:620px;margin:32px auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
    <div style="background:#1e3a5f;padding:24px;color:#fff"><h1 style="margin:0;font-size:20px">Reporte de gestión de PQR</h1></div>
    <div style="padding:28px">
        <p>Hola.</p>
        <p>Adjuntamos el reporte de PQR de <strong>{{ \App\Models\AppSetting::current()->residential_name }}</strong> correspondiente al periodo <strong>{{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d/m/Y') }}</strong> a <strong>{{ \Illuminate\Support\Carbon::parse($dateTo)->format('d/m/Y') }}</strong>.</p>
        <p style="color:#6b7280;font-size:13px">Este reporte fue generado manualmente desde el sistema de gestión de PQR.</p>
    </div>
</div></body></html>
