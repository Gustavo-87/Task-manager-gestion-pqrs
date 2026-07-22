<?php

namespace App\Http\Controllers;

use App\Mail\PqrReportMail;
use App\Services\AuditLogger;
use App\Services\PqrReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use App\Models\Audit;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request, PqrReportService $reports): View
    {
        $dateFrom = $request->date('date_from')?->toDateString() ?? Carbon::today()->subDays(90)->toDateString();
        $dateTo = $request->date('date_to')?->toDateString() ?? Carbon::today()->toDateString();

        if (Carbon::parse($dateTo)->isBefore(Carbon::parse($dateFrom))) {
            $dateFrom = Carbon::today()->subDays(90)->toDateString();
            $dateTo = Carbon::today()->toDateString();
        }

        $preview = $reports->data($dateFrom, $dateTo);
        $attended = $preview['byStatus']['resuelta'] + $preview['byStatus']['cerrada'];
        $attendedPercentage = $preview['pqrs']->isEmpty()
            ? 0
            : (int) round(($attended / $preview['pqrs']->count()) * 100);

        $recentReports = Audit::with('user')
            ->where('module', 'Reportes')
            ->whereIn('action', ['descargar', 'enviar_correo'])
            ->latest()
            ->limit(5)
            ->get();

        return view('reports.index', compact(
            'dateFrom', 'dateTo', 'preview', 'attendedPercentage', 'recentReports'
        ));
    }

    public function download(Request $request, PqrReportService $reports): Response
    {
        $dates = $this->validateDates($request);
        $pdf = $reports->pdf($dates['date_from'], $dates['date_to']);
        $metadata = ['date_from' => $dates['date_from'], 'date_to' => $dates['date_to']];

        AuditLogger::log($request, 'Reportes', 'generar', 'Generó un reporte PDF de PQRS.', null, null, $metadata);
        AuditLogger::log($request, 'Reportes', 'descargar', 'Descargó un reporte PDF de PQRS.', null, null, $metadata);

        return $pdf->download($reports->filename($dates['date_from'], $dates['date_to']));
    }

    public function email(Request $request, PqrReportService $reports): RedirectResponse
    {
        $dates = $this->validateDates($request);
        $filename = $reports->filename($dates['date_from'], $dates['date_to']);
        $metadata = ['date_from' => $dates['date_from'], 'date_to' => $dates['date_to'], 'recipient' => $request->user()->email];

        AuditLogger::log($request, 'Reportes', 'generar', 'Generó un reporte PDF de PQRS para envío.', null, null, $metadata);

        try {
            Mail::to($request->user())->send(new PqrReportMail(
                $dates['date_from'],
                $dates['date_to'],
                $reports->pdf($dates['date_from'], $dates['date_to'])->output(),
                $filename,
            ));
            AuditLogger::log($request, 'Reportes', 'enviar_correo', "Envió el reporte PDF a {$request->user()->email}.", null, null, $metadata);

            return back()->with('success', "Reporte enviado a {$request->user()->email}.");
        } catch (Throwable $exception) {
            report($exception);
            AuditLogger::log($request, 'Reportes', 'fallo_correo', 'No fue posible enviar el reporte PDF.', null, null, $metadata);

            return back()->withErrors(['email' => 'No fue posible enviar el reporte. Revisa la configuración de correo.'])->withInput();
        }
    }

    private function validateDates(Request $request): array
    {
        return $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
        ], [], [
            'date_from' => 'fecha inicial',
            'date_to' => 'fecha final',
        ]);
    }
}
