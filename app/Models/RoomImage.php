<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomImage extends Model
{
    protected $table = 'public.room_images';
    
    public $timestamps = false;

    protected $fillable = [
        'room_id',
        'image_url', // FIXED: Aligned with your exact SQL schema
        'display_order',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    protected function casts(): array
    {
        return [
            'room_id' => 'integer',
            'display_order' => 'integer',
        ];
    }
}