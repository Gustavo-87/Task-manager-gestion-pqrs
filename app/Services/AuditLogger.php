<?php

namespace App\Services;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    private const SENSITIVE = ['password', 'password_confirmation', 'remember_token'];

    public static function log(
        Request $request,
        string $module,
        string $action,
        string $description,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): Audit {
        return Audit::create([
            'user_id' => $request->user()?->id,
            'module' => $module,
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'description' => $description,
            'old_values' => self::sanitize($oldValues),
            'new_values' => self::sanitize($newValues),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private static function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        return collect($values)->except(self::SENSITIVE)->all();
    }
}
