<?php

namespace App\Http\Controllers;

use App\Application\Contexto\ContextoOperativo;
use App\Application\Pqrs\ConsultaPqrsContextuales;
use App\Models\Pqr;
use App\Models\TipoPqr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\PqrEventNotification;
use App\Models\AutomationRule;
use App\Models\PqrTag;
use App\Models\ResponseTemplate;

class PqrController extends Controller
{
    public function index(
        Request $request,
        ContextoOperativo $contexto,
        ConsultaPqrsContextuales $consultaPqrs
    )
    {
        $this->authorize('viewAny', Pqr::class);

        $baseQuery = $consultaPqrs->para($contexto);
        if (! $request->user()->canViewAllPqrs()) {
            $baseQuery->where('user_id', $request->user()->id);
        }

        $query = (clone $baseQuery)->with(['user', 'tipoPqr', 'assignee']);

        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        if ($request->estado === 'pendientes') {
            $query->whereIn('estado', ['radicada', 'en_revision']);
        } elseif ($request->estado === 'por_vencer') {
            $query->whereIn('estado', ['radicada', 'en_revision'])
                ->whereBetween('fecha_limite_respuesta', [Carbon::today(), Carbon::today()->addDays(3)]);
        } elseif ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('tipo_pqr_id')) $query->where('tipo_pqr_id', $request->integer('tipo_pqr_id'));
        if ($request->filled('assigned_to_id')) $query->where('assigned_to_id', $request->integer('assigned_to_id'));
        if ($request->filled('desde')) $query->whereDate('fecha_radicacion', '>=', $request->date('desde'));
        if ($request->filled('hasta')) $query->whereDate('fecha_radicacion', '<=', $request->date('hasta'));

        $pqrs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $resumen = [
            'total' => (clone $baseQuery)->count(),
            'pendientes' => (clone $baseQuery)->whereIn('estado', ['radicada', 'en_revision'])->count(),
            'respondidas' => (clone $baseQuery)->where('estado', 'respondida')->count(),
            'por_vencer' => (clone $baseQuery)->whereIn('estado', ['radicada', 'en_revision'])
                ->whereBetween('fecha_limite_respuesta', [Carbon::today(), Carbon::today()->addDays(3)])
                ->count(),
        ];

        $months = collect(range(5, 0))->map(fn ($offset) => Carbon::now()->subMonths($offset));
        $createdByMonth = (clone $baseQuery)->where('created_at', '>=', $months->first()->copy()->startOfMonth())
            ->get(['created_at'])->groupBy(fn ($pqr) => $pqr->created_at->format('Y-m'))->map->count();
        $chartData = [
            'months' => $months->map(fn ($month) => [
                'label' => $month->translatedFormat('M'),
                'value' => $createdByMonth->get($month->format('Y-m'), 0),
            ]),
            'states' => (clone $baseQuery)->select('estado', DB::raw('count(*) as total'))
                ->groupBy('estado')->pluck('total', 'estado'),
        ];

        $tipos = TipoPqr::orderBy('nombre')->get();
        $gestores = User::whereIn('role', ['admin','gestor','apoyo'])->orderBy('name')->get();
        return view('pqrs.index', compact('pqrs', 'resumen', 'chartData', 'tipos', 'gestores'));
    }

    public function create()
    {
        $this->authorize('create', Pqr::class);
        $tipos = TipoPqr::orderBy('nombre')->get();

        return view('pqrs.create', compact('tipos'));
    }

    public function store(Request $request, ContextoOperativo $contexto)
    {
        $request->validate([
            'asunto' => 'required|string|max:150',
            'descripcion' => 'required|string',
            'fecha_radicacion' => 'required|date',
            'fecha_limite_respuesta' => 'nullable|date',
            'tipo_pqr_id' => 'required|exists:tipo_pqrs,id',
            'adjuntos' => ['nullable', 'array', 'max:8'],
            'adjuntos.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt,csv,zip'],
        ]);

        $this->authorize('create', Pqr::class);
        $data = $request->only(['asunto', 'descripcion', 'fecha_radicacion', 'fecha_limite_respuesta', 'tipo_pqr_id']);

        $pqr = DB::transaction(function () use ($contexto, $data, $request) {
            if ($contexto->copropiedad->organizacion_id !== $contexto->organizacion->id) {
                throw new \RuntimeException(
                    'No es posible radicar la PQR porque el contexto institucional es inconsistente.'
                );
            }

            $pqr = new Pqr($data);
            $pqr->user()->associate($request->user());
            $pqr->organizacion()->associate($contexto->organizacion);
            $pqr->copropiedad()->associate($contexto->copropiedad);
            $pqr->save();
            if ($rule = AutomationRule::where('active', true)->where(fn($q) => $q->whereNull('tipo_pqr_id')->orWhere('tipo_pqr_id', $pqr->tipo_pqr_id))->first()) {
                $pqr->update(['assigned_to_id' => $rule->assign_to_id, 'estado' => $rule->set_status]);
            }
            $this->storeAttachments($request, $pqr);
            $pqr->activities()->create(['user_id' => $request->user()->id, 'action' => 'created', 'description' => 'Radicó la solicitud.']);

            return $pqr;
        });
        User::whereIn('role', ['admin','gestor'])->get()->each->notify(new PqrEventNotification($pqr, 'Nueva solicitud radicada', "Se creó la PQR-".str_pad($pqr->id, 4, '0', STR_PAD_LEFT).": {$pqr->asunto}"));

        return redirect()->route('pqrs.show', $pqr)->with('success', 'PQR radicada correctamente. Ya no puede ser modificada.');
    }

    public function show(Pqr $pqr)
    {
        $this->authorize('view', $pqr);
        $pqr->load(['user', 'tipoPqr', 'attachments', 'assignee', 'activities.user', 'replies.user', 'internalComments.user', 'tags', 'satisfactionSurvey']);
        $templates = request()->user()->canManagePqrs() ? ResponseTemplate::orderBy('name')->get() : collect();
        $availableTags = request()->user()->canManagePqrs() ? PqrTag::orderBy('name')->get() : collect();

        return view('pqrs.show', compact('pqr', 'templates', 'availableTags'));
    }

    public function edit(Pqr $pqr)
    {
        $this->authorize('update', $pqr);
        $tipos = TipoPqr::orderBy('nombre')->get();
        $gestores = User::whereIn('role', ['admin','gestor','apoyo'])->orderBy('name')->get();

        return view('pqrs.edit', compact('pqr', 'tipos', 'gestores'));
    }

    public function update(Request $request, Pqr $pqr)
    {
        $request->validate([
            'asunto' => 'required|string|max:150',
            'descripcion' => 'required|string',
            'fecha_radicacion' => 'required|date',
            'fecha_limite_respuesta' => 'nullable|date',
            'estado' => 'required|in:radicada,en_revision,respondida,cerrada',
            'tipo_pqr_id' => 'required|exists:tipo_pqrs,id',
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'adjuntos' => ['nullable', 'array', 'max:8'],
            'adjuntos.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt,csv,zip'],
        ]);

        $this->authorize('update', $pqr);
        $before = $pqr->only(['estado', 'assigned_to_id']);
        $pqr->update($request->only(['asunto', 'descripcion', 'fecha_radicacion', 'fecha_limite_respuesta', 'estado', 'tipo_pqr_id', 'assigned_to_id']));
        $this->storeAttachments($request, $pqr);
        $changes = [];
        if ($before['estado'] !== $pqr->estado) $changes[] = "Cambió el estado a {$pqr->estado_label}.";
        if ((int) $before['assigned_to_id'] !== (int) $pqr->assigned_to_id) $changes[] = 'Asignó la solicitud a '.($pqr->assignee?->name ?? 'Sin responsable').'.';
        $pqr->activities()->create(['user_id' => $request->user()->id, 'action' => 'updated', 'description' => $changes ? implode(' ', $changes) : 'Actualizó la información de la solicitud.', 'metadata' => ['before' => $before]]);
        if ($before['estado'] !== $pqr->estado) $pqr->user?->notify(new PqrEventNotification($pqr, 'Estado de solicitud actualizado', "La PQR-".str_pad($pqr->id, 4, '0', STR_PAD_LEFT)." ahora está {$pqr->estado_label}."));
        if ((int) $before['assigned_to_id'] !== (int) $pqr->assigned_to_id && $pqr->assignee) $pqr->assignee->notify(new PqrEventNotification($pqr, 'Solicitud asignada', "Te asignaron la PQR-".str_pad($pqr->id, 4, '0', STR_PAD_LEFT).'.'));

        return redirect()->route('pqrs.index')->with('success', 'PQR actualizada correctamente.');
    }

    public function destroy(Pqr $pqr)
    {
        $this->authorize('delete', $pqr);
        foreach ($pqr->attachments as $attachment) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($attachment->path);
        }
        $pqr->delete();

        return redirect()->route('pqrs.index')->with('success', 'PQR eliminada correctamente.');
    }

    private function storeAttachments(Request $request, Pqr $pqr): void
    {
        foreach ($request->file('adjuntos', []) as $file) {
            $path = $file->store("pqrs/{$pqr->id}");
            $pqr->attachments()->create([
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }
    }
}
