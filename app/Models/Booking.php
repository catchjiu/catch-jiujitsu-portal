<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'class_id',
        'booked_at',
    ];

    protected $casts = [
        'booked_at' => 'datetime',
    ];

    /**
     * Get the user that made the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the class that was booked.
     */
    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'class_id');
    }
}
