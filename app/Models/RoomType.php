<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    protected $table = 'public.room_types';
    
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'capacity',
        'base_price',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'room_type_id');
    }

    public function amenities(): BelongsToMany
    {
        // FIXED: Pointed to your real pivot table name
        return $this->belongsToMany(
            Amenity::class, 
            'public.room_amenities', 
            'room_type_id', 
            'amenity_id'
        );
    }

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'base_price' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }
}