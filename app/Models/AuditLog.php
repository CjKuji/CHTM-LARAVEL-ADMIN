<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'public.audit_logs';

    // Matches the bigint structure in your SQL file
    protected $keyType = 'int'; 
    
    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'old_value',
        'new_value',
        'changed_by',
        'reason',
    ];

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    protected function casts(): array
    {
        return [
            // Changed to clean native arrays for tracking model alterations
            'old_value' => 'array', 
            'new_value' => 'array',
            'created_at' => 'datetime',
        ];
    }
}