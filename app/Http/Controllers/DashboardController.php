<?php

namespace App\Http\Controllers;

use App\Models\Pqr;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $baseQuery = Pqr::query();

        if ($request->user()->rol !== 'admin') {
            $baseQuery->where('user_id', $request->user()->id);
        }

        $counts = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN estado = 'radicada' THEN 1 ELSE 0 END) as radicadas")
            ->selectRaw("SUM(CASE WHEN estado = 'en_revision' THEN 1 ELSE 0 END) as en_revision")
            ->selectRaw("SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso")
            ->selectRaw("SUM(CASE WHEN estado = 'en_espera' THEN 1 ELSE 0 END) as en_espera")
            ->selectRaw("SUM(CASE WHEN estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas")
            ->selectRaw("SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as resueltas")
            ->selectRaw("SUM(CASE WHEN estado = 'cerrada' THEN 1 ELSE 0 END) as cerradas")
            ->first();

        $today = Carbon::today();
        $deadline = $today->copy()->addDay();

        $overdue = (clone $baseQuery)
            ->whereNotIn('estado', Pqr::inactiveStatuses())
            ->whereDate('fecha_limite_respuesta', '<', $today)
            ->count();

        $upcoming = (clone $baseQuery)
            ->with(['user', 'tipoPqr'])
            ->whereNotIn('estado', Pqr::inactiveStatuses())
            ->whereBetween('fecha_limite_respuesta', [$today, $deadline])
            ->orderBy('fecha_limite_respuesta')
            ->limit(5)
            ->get();

        $recent = (clone $baseQuery)
            ->with(['user', 'tipoPqr'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('counts', 'overdue', 'upcoming', 'recent'));
    }
}
