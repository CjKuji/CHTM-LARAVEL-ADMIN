<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HousekeepingTask extends Model
{
    protected $table = 'public.housekeeping_tasks';

    public $timestamps = false;

    protected $fillable = [
        'room_id',
        'booking_id',
        'template_id',
        'assigned_to',
        'status',
        'note',
        'duration_minutes',
        'completed_by',
        'started_at',
        'completed_at',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(HousekeepingTemplate::class, 'template_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HousekeepingTaskItem::class, 'task_id');
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}