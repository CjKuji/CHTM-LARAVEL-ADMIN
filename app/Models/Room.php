<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $table = 'public.rooms';

    public $timestamps = false;

    protected $fillable = [
        'room_type_id',
        'room_number',
        'floor',
        'price_override',
        'status',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'room_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class, 'room_id');
    }

    public function housekeepingTasks(): HasMany
    {
        return $this->hasMany(HousekeepingTask::class, 'room_id');
    }

    protected function casts(): array
    {
        return [
            'floor' => 'integer',
            'price_override' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }
}