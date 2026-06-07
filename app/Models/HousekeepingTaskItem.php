<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HousekeepingTaskItem extends Model
{
    protected $table = 'public.housekeeping_task_items';

    public $timestamps = false;

    protected $fillable = [
        'task_id', 
        'item_name', 
        'note', 
        'quantity', 
        'is_done'
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(HousekeepingTask::class, 'task_id');
    }

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
            'quantity' => 'integer',
        ];
    }
}