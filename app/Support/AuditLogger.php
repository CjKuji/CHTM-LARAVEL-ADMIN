<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class AuditLogger
{
    /**
     * Write an operational event to the tracking logs database.
     * * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public static function log(
        string $entityType,
        int $entityId,
        string $action,
        ?array $old = null,
        ?array $new = null,
        ?User $actor = null,
        ?string $reason = null
    ): void {
        // FIXED: Safely resolve actor ID using active state sessions if parameter is blank
        $actorId = $actor?->id ?? Auth::id();

        AuditLog::query()->create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => strtoupper($action),
            'old_value' => $old,
            'new_value' => $new,
            'changed_by' => $actorId,
            'reason' => $reason,
        ]);
    }
}