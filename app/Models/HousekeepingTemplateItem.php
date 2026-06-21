<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HousekeepingTemplateItem extends Model
{
    protected $table = 'public.housekeeping_template_items';

    public $timestamps = false;

    protected $fillable = [
        'template_id', 
        'item_name', 
        'default_quantity'
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(HousekeepingTemplate::class, 'template_id');
    }

    protected function casts(): array
    {
        return [
            'default_quantity' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}