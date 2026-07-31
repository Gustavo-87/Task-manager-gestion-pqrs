<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Pqr extends Model
{
    use HasFactory;
    protected $fillable = [
        'asunto',
        'descripcion',
        'fecha_radicacion',
        'fecha_limite_respuesta',
        'estado',
        'user_id',
        'assigned_to_id',
        'tipo_pqr_id',
        'last_reminder_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_radicacion' => 'date',
            'fecha_limite_respuesta' => 'date', 'last_reminder_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organizacion(): BelongsTo
    {
        return $this->belongsTo(Organizacion::class);
    }

    public function copropiedad(): BelongsTo
    {
        return $this->belongsTo(Copropiedad::class);
    }

    public function tipoPqr()
    {
        return $this->belongsTo(TipoPqr::class);
    }

    public function assignee() { return $this->belongsTo(User::class, 'assigned_to_id'); }
    public function activities() { return $this->hasMany(PqrActivity::class)->latest(); }
    public function replies() { return $this->hasMany(PqrReply::class)->latest(); }
    public function internalComments() { return $this->hasMany(PqrInternalComment::class)->latest(); }
    public function tags() { return $this->belongsToMany(PqrTag::class); }
    public function satisfactionSurvey() { return $this->hasOne(SatisfactionSurvey::class); }

    public function attachments()
    {
        return $this->hasMany(PqrAttachment::class);
    }

    public function scopeRespondidas($query)
    {
        return $query->where('estado', 'respondida');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', '!=', 'respondida');
    }

    public function scopeBuscar($query, $texto)
    {
        return $query->where(function ($query) use ($texto) {
            $query->where('asunto', 'LIKE', "%{$texto}%")
                ->orWhere('descripcion', 'LIKE', "%{$texto}%")
                ->orWhere('id', $texto)
                ->orWhereHas('user', fn ($user) => $user->where('name', 'LIKE', "%{$texto}%")->orWhere('email', 'LIKE', "%{$texto}%"))
                ->orWhereHas('assignee', fn ($user) => $user->where('name', 'LIKE', "%{$texto}%"));
        });
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'en_revision' => 'En revisión',
            'respondida' => 'Respondida',
            'cerrada' => 'Cerrada',
            default => 'Radicada',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return (bool) ($this->fecha_limite_respuesta?->isPast()
            && ! in_array($this->estado, ['respondida', 'cerrada'], true));
    }
    public function getElapsedDaysAttribute(): int { return $this->fecha_radicacion->diffInDays(now()); }
    public function getRemainingDaysAttribute(): ?int { return $this->fecha_limite_respuesta ? now()->startOfDay()->diffInDays($this->fecha_limite_respuesta, false) : null; }
}
