<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HousekeepingTemplate extends Model
{
    protected $table = 'public.housekeeping_templates';

    public $timestamps = false;

    protected $fillable = [
        'room_type_id', 
        'name', 
        'description'
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HousekeepingTemplateItem::class, 'template_id');
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}