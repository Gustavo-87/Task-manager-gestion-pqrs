<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Pqr;
use App\Models\PqrHistory;
use App\Models\TipoPqr;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PqrController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Pqr::class);

        $query = Pqr::with([
            'user:id,name,email',
            'tipoPqr:id,nombre,descripcion',
            'responder:id,name,email',
        ]);

        if ($request->user()->rol !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('buscar')) {
            $query->buscar($request->string('buscar')->toString());
        }

        if ($request->filled('estado')) {
            $request->validate([
                'estado' => [
                    Rule::in([
                        'radicada',
                        'en_revision',
                        'respondida',
                        'cerrada',
                    ]),
                ],
            ]);

            $query->where('estado', $request->estado);
        }

        $pqrs = $query
            ->latest()
            ->get();

        return response()->json([
            'data' => $pqrs,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Pqr::class);

        $validated = $request->validate([
            'asunto' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string'],
            'fecha_radicacion' => ['required', 'date'],
            'tipo_pqr_id' => [
                'required',
                Rule::exists('tipo_pqrs', 'id')
                    ->where('activo', true),
            ],
        ]);

        $pqr = DB::transaction(function () use ($request, $validated): Pqr {
            $validated['fecha_limite_respuesta'] = Carbon::parse(
                $validated['fecha_radicacion']
            )
                ->addDays(AppSetting::current()->response_days)
                ->toDateString();

            $validated['user_id'] = $request->user()->id;
            $validated['estado'] = 'radicada';

            $pqr = Pqr::create($validated);

            AuditLogger::log(
                $request,
                'PQR',
                'crear',
                "Creó la PQR #{$pqr->id}: {$pqr->asunto}",
                $pqr,
                null,
                $pqr->getAttributes(),
            );

            return $pqr;
        });

        return response()->json([
            'message' => 'PQR creada correctamente.',
            'data' => $pqr->load([
                'user:id,name,email',
                'tipoPqr:id,nombre,descripcion',
            ]),
        ], 201);
    }

    public function show(Request $request, Pqr $pqr): JsonResponse
    {
        Gate::authorize('view', $pqr);

        return response()->json([
            'data' => $pqr->load([
                'user:id,name,email',
                'tipoPqr:id,nombre,descripcion',
                'responder:id,name,email',
            ]),
        ]);
    }

    public function update(Request $request, Pqr $pqr): JsonResponse
    {
        Gate::authorize('update', $pqr);

        $validated = $request->validate([
            'estado' => [
                'required',
                Rule::in([
                    'radicada',
                    'en_revision',
                    'respondida',
                    'cerrada',
                ]),
            ],
        ]);

        DB::transaction(function () use ($request, $pqr, $validated): void {
            $estadoAnterior = $pqr->estado;

            $pqr->update($validated);

            if ($estadoAnterior === $pqr->estado) {
                return;
            }

            PqrHistory::create([
                'pqr_id' => $pqr->id,
                'campo' => 'estado',
                'valor_anterior' => $estadoAnterior,
                'valor_nuevo' => $pqr->estado,
                'user_id' => $request->user()->id,
            ]);

            AuditLogger::log(
                $request,
                'PQR',
                'cambiar_estado',
                "Cambió el estado de la PQR #{$pqr->id}",
                $pqr,
                ['estado' => $estadoAnterior],
                ['estado' => $pqr->estado],
            );
        });

        return response()->json([
            'message' => 'Estado de la PQR actualizado correctamente.',
            'data' => $pqr->fresh()->load([
                'user:id,name,email',
                'tipoPqr:id,nombre,descripcion',
                'responder:id,name,email',
            ]),
        ]);
    }

    public function destroy(Request $request, Pqr $pqr): JsonResponse
    {
        Gate::authorize('delete', $pqr);

        DB::transaction(function () use ($request, $pqr): void {
            $snapshot = $pqr->getAttributes();

            AuditLogger::log(
                $request,
                'PQR',
                'eliminar',
                "Eliminó la PQR #{$pqr->id}: {$pqr->asunto}",
                $pqr,
                $snapshot,
            );

            $pqr->delete();
        });

        return response()->json(null, 204);
    }
}
