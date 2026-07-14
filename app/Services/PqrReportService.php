<?php

namespace App\Services;

use App\Models\Pqr;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use App\Models\AppSetting;

class PqrReportService
{
    public function data(string $dateFrom, string $dateTo): array
    {
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();
        $today = today();

        $pqrs = Pqr::with(['user', 'tipoPqr'])
            ->whereBetween('fecha_radicacion', [$from->toDateString(), $to->toDateString()])
            ->orderBy('fecha_radicacion')
            ->get();

        return [
            'dateFrom' => $from,
            'dateTo' => $to,
            'generatedAt' => now(),
            'settings' => AppSetting::current(),
            'pqrs' => $pqrs,
            'byStatus' => collect(['radicada', 'en_revision', 'respondida', 'cerrada'])
                ->mapWithKeys(fn ($status) => [$status => $pqrs->where('estado', $status)->count()]),
            'byCategory' => $pqrs->groupBy(fn ($pqr) => $pqr->tipoPqr?->nombre ?? 'Sin categoría')
                ->map->count()
                ->sortKeys(),
            'overdue' => $pqrs->filter(fn ($pqr) => ! in_array($pqr->estado, ['respondida', 'cerrada'], true)
                && $pqr->fecha_limite_respuesta->isBefore($today)),
            'upcoming' => $pqrs->filter(fn ($pqr) => ! in_array($pqr->estado, ['respondida', 'cerrada'], true)
                && $pqr->fecha_limite_respuesta->isSameDay($today->copy()->addDay())),
        ];
    }

    public function pdf(string $dateFrom, string $dateTo): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('reports.pdf', $this->data($dateFrom, $dateTo))
            ->setPaper('a4', 'landscape');
    }

    public function filename(string $dateFrom, string $dateTo): string
    {
        return "reporte-pqrs-{$dateFrom}-a-{$dateTo}.pdf";
    }
}
