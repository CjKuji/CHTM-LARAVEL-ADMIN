<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class Booking extends Model
{
    use HasFactory; 

    protected $table = 'public.bookings';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'room_id',
        'start_at',
        'end_at',
        'guests',
        'extra_beds',
        'price_at_booking',
        'total_amount',
        'message',
        'status',
        'payment_method',
        'has_child',
        'child_age_group',
        'has_pwd',
        'has_senior',
        'checked_in_at',
        'checked_out_at',
        'approved_by',
        'rejected_by',
        'checked_in_by',
        'checked_out_by',
    ];

    /**
     * Relationship tracking link pointing to the customer profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship link mapping to physical inventory unit
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function checkedInByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function checkedOutByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    /**
     * Automatic Type Casting Definition Array
     */
    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'has_child' => 'boolean',
            'has_pwd' => 'boolean',
            'has_senior' => 'boolean',
            'price_at_booking' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }
}