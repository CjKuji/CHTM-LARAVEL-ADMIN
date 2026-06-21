<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomStatusLog extends Model
{
    protected $table = 'public.room_status_logs';
    
    public $timestamps = false;

    protected $fillable = [
        'room_id',
        'changed_by',
        'note',
        'status',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    protected function casts(): array
    {
        return [
            'room_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}