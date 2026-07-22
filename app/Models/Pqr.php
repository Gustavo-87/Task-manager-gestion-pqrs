<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pqr extends Model
{
    use HasFactory;

    public const STATUS_LABELS = [
        'radicada' => 'Radicada',
        'en_revision' => 'En revisión',
        'en_proceso' => 'En proceso',
        'en_espera' => 'En espera',
        'rechazada' => 'Rechazada',
        'resuelta' => 'Resuelta',
        'cerrada' => 'Cerrada',
    ];

    public const WORKFLOW_TRANSITIONS = [
        'enviar_revision' => [
            'from' => ['radicada'],
            'to' => 'en_revision',
            'label' => 'Enviar a revisión',
        ],
        'asignar_proceso' => [
            'from' => ['en_revision'],
            'to' => 'en_proceso',
            'label' => 'Asignar a proceso',
        ],
        'rechazar' => [
            'from' => ['en_revision'],
            'to' => 'rechazada',
            'label' => 'Rechazar',
        ],
        'reabrir_rechazada' => [
            'from' => ['rechazada'],
            'to' => 'en_revision',
            'label' => 'Reabrir',
        ],
        'poner_espera' => [
            'from' => ['en_proceso'],
            'to' => 'en_espera',
            'label' => 'Poner en espera',
        ],
        'reactivar' => [
            'from' => ['en_espera'],
            'to' => 'en_proceso',
            'label' => 'Reactivar',
        ],
        'reabrir_resuelta' => [
            'from' => ['resuelta'],
            'to' => 'en_revision',
            'label' => 'Reabrir',
        ],
        'cerrar' => [
            'from' => ['resuelta'],
            'to' => 'cerrada',
            'label' => 'Cerrar',
        ],
    ];

    protected $fillable = [
        'asunto',
        'descripcion',
        'respuesta',
        'fecha_radicacion',
        'fecha_limite_respuesta',
        'respondida_en',
        'estado',
        'user_id',
        'respondida_por',
        'tipo_pqr_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_radicacion' => 'date',
            'fecha_limite_respuesta' => 'date',
            'respondida_en' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tipoPqr()
    {
        return $this->belongsTo(TipoPqr::class);
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'respondida_por');
    }

    public function histories()
    {
        return $this->hasMany(PqrHistory::class);
    }

    public function scopeRespondidas($query)
    {
        return $query->whereIn('estado', ['resuelta', 'cerrada']);
    }

    public function scopePendientes($query)
    {
        return $query->whereNotIn('estado', self::inactiveStatuses());
    }

    public function scopeBuscar($query, $texto)
    {
        return $query->where('asunto', 'LIKE', "%{$texto}%");
    }

    public static function statuses(): array
    {
        return self::STATUS_LABELS;
    }

    public static function statusLabel(string $status): string
    {
        return self::statuses()[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public static function workflowTransitions(): array
    {
        return self::WORKFLOW_TRANSITIONS;
    }

    public static function transitionForAction(string $action): ?array
    {
        return self::workflowTransitions()[$action] ?? null;
    }

    public static function canTransitionTo(string $currentStatus, string $nextStatus): bool
    {
        foreach (self::workflowTransitions() as $transition) {
            if (in_array($currentStatus, $transition['from'], true) && $transition['to'] === $nextStatus) {
                return true;
            }
        }

        return false;
    }

    public static function canApplyWorkflowAction(string $currentStatus, string $action): bool
    {
        $transition = self::transitionForAction($action);

        return $transition !== null
            && in_array($currentStatus, $transition['from'], true);
    }

    public static function workflowActionsFor(string $currentStatus): array
    {
        return collect(self::workflowTransitions())
            ->filter(fn (array $transition) => in_array($currentStatus, $transition['from'], true))
            ->all();
    }

    public static function responseEligibleStatuses(): array
    {
        return ['en_proceso'];
    }

    public static function responseCompletionStatuses(): array
    {
        return ['resuelta', 'cerrada'];
    }

    public static function inactiveStatuses(): array
    {
        return ['rechazada', 'resuelta', 'cerrada'];
    }
}
