<?php

namespace App\Http\Controllers;

use App\Application\Autorizacion\AutorizacionContextual;
use App\Application\Contexto\ContextoOperativo;
use App\Application\Pqrs\ConsultaPqrsContextuales;
use App\Models\{AuditLog, AutomationRule, Pqr, PqrTag, ResponseTemplate, SatisfactionSurvey, TipoPqr, User};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ComplementaryController extends Controller
{
    private function permitir(ContextoOperativo $contexto, string $permiso): void
    {
        abort_unless(app(AutorizacionContextual::class)->tienePermiso($contexto, $permiso), 403);
    }

    public function workload(Request $request, ContextoOperativo $contexto, ConsultaPqrsContextuales $consulta): View
    {
        $this->permitir($contexto, 'gestion.carga_ver');
        $scope = fn ($query) => $consulta->restringir($query, $contexto);
        $users = User::query()->whereHas('membresiasCopropiedad', fn ($query) => $query->where('organizacion_id', $contexto->organizacion->id)->where('copropiedad_id', $contexto->copropiedad->id)->where('estado', 'activa'))->withCount(['assignedPqrs as active_count' => fn ($query) => $scope($query)->whereIn('estado', ['radicada', 'en_revision']), 'assignedPqrs as overdue_count' => fn ($query) => $scope($query)->whereIn('estado', ['radicada', 'en_revision'])->whereDate('fecha_limite_respuesta', '<', today()), 'assignedPqrs as completed_count' => fn ($query) => $scope($query)->whereIn('estado', ['respondida', 'cerrada'])])->get();
        return view('management.workload', compact('users'));
    }

    public function tools(Request $request, ContextoOperativo $contexto): View
    {
        $this->permitir($contexto, 'gestion.herramientas_gestionar');
        return view('management.tools', ['templates' => ResponseTemplate::latest()->get(), 'tags' => PqrTag::query()->where('organizacion_id', $contexto->organizacion->id)->where('copropiedad_id', $contexto->copropiedad->id)->orderBy('name')->get(), 'rules' => AutomationRule::with(['type', 'assignee'])->latest()->get(), 'types' => TipoPqr::orderBy('nombre')->get(), 'managers' => User::query()->whereHas('membresiasCopropiedad', fn ($query) => $query->where('organizacion_id', $contexto->organizacion->id)->where('copropiedad_id', $contexto->copropiedad->id)->where('estado', 'activa'))->orderBy('name')->get()]);
    }

    public function template(Request $request, ContextoOperativo $contexto): RedirectResponse { $this->permitir($contexto, 'gestion.herramientas_gestionar'); ResponseTemplate::create($request->validate(['name' => 'required|max:100', 'subject' => 'nullable|max:150', 'body' => 'required|max:10000']) + ['created_by' => $request->user()->id]); return back()->with('success', 'Plantilla creada.'); }
    public function tag(Request $request, ContextoOperativo $contexto): RedirectResponse { $this->permitir($contexto, 'gestion.herramientas_gestionar'); $data = $request->validate(['name' => ['required', 'max:60', Rule::unique('pqr_tags', 'name')->where(fn ($query) => $query->where('copropiedad_id', $contexto->copropiedad->id))], 'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/']); $tag = new PqrTag($data); $tag->organizacion()->associate($contexto->organizacion); $tag->copropiedad()->associate($contexto->copropiedad); $tag->save(); return back()->with('success', 'Etiqueta creada.'); }
    public function rule(Request $request, ContextoOperativo $contexto): RedirectResponse { $this->permitir($contexto, 'gestion.herramientas_gestionar'); AutomationRule::create($request->validate(['name' => 'required|max:100', 'tipo_pqr_id' => 'nullable|exists:tipo_pqrs,id', 'assign_to_id' => 'nullable|exists:users,id', 'set_status' => 'required|in:radicada,en_revision'])); return back()->with('success', 'Regla automática creada.'); }
    public function syncTags(Request $request, Pqr $pqr, ContextoOperativo $contexto): RedirectResponse { $this->authorize('update', $pqr); abort_unless($pqr->organizacion_id === $contexto->organizacion->id && $pqr->copropiedad_id === $contexto->copropiedad->id, 404); $data = $request->validate(['tags' => 'nullable|array', 'tags.*' => [Rule::exists('pqr_tags', 'id')->where(fn ($query) => $query->where('organizacion_id', $contexto->organizacion->id)->where('copropiedad_id', $contexto->copropiedad->id))]]); $pqr->tags()->syncWithPivotValues($data['tags'] ?? [], ['organizacion_id' => $contexto->organizacion->id, 'copropiedad_id' => $contexto->copropiedad->id]); $pqr->activities()->create(['user_id' => $request->user()->id, 'action' => 'tags_updated', 'description' => 'Actualizó las etiquetas de la solicitud.']); return back()->with('success', 'Etiquetas actualizadas.'); }
    public function residents(Request $request, ContextoOperativo $contexto): View { $this->permitir($contexto, 'residentes.gestionar'); return view('management.residents', ['residents' => User::orderBy('name')->paginate(20)]); }
    public function resident(Request $request, User $user, ContextoOperativo $contexto): RedirectResponse { $this->permitir($contexto, 'residentes.gestionar'); $user->update($request->validate(['tower' => 'nullable|max:50', 'unit' => 'nullable|max:50'])); return back()->with('success', 'Unidad residencial actualizada.'); }
    public function audit(Request $request, ContextoOperativo $contexto): View { $this->permitir($contexto, 'auditoria.ver'); return view('management.audit', ['logs' => AuditLog::with('user')->latest()->paginate(30)]); }
    public function survey(Request $request, Pqr $pqr): RedirectResponse { $this->authorize('view', $pqr); abort_unless($pqr->user_id === $request->user()->id && in_array($pqr->estado, ['respondida', 'cerrada']), 403); $data = $request->validate(['rating' => 'required|integer|between:1,5', 'comment' => 'nullable|max:2000']); SatisfactionSurvey::updateOrCreate(['pqr_id' => $pqr->id], $data + ['user_id' => $request->user()->id]); return back()->with('success', 'Gracias por calificar la atención.'); }
}
