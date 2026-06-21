<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    protected $table = 'public.amenities';
    
    public $timestamps = false; 

    protected $fillable = [
        'name',
    ];

    public function roomTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            RoomType::class, 
            'public.room_amenities', 
            'amenity_id', 
            'room_type_id'
        );
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}