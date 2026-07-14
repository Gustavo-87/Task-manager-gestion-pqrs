<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'total' => Audit::count(),
            'today' => Audit::whereDate('created_at', today())->count(),
            'users' => Audit::whereNotNull('user_id')->distinct()->count('user_id'),
            'pqrs' => Audit::where('module', 'PQR')->whereNotNull('auditable_id')->distinct()->count('auditable_id'),
        ];

        $audits = Audit::with('user')
            ->when($request->filled('buscar'), fn ($query) => $query->where('description', 'like', '%'.$request->string('buscar')->trim().'%'))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('module'), fn ($query) => $query->where('module', $request->string('module')))
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('audits.index', [
            'audits' => $audits,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'modules' => Audit::query()->distinct()->orderBy('module')->pluck('module'),
            'actions' => Audit::query()->distinct()->orderBy('action')->pluck('action'),
            'stats' => $stats,
        ]);
    }

    public function show(Audit $audit): View
    {
        return view('audits.show', ['audit' => $audit->load('user')]);
    }
}
