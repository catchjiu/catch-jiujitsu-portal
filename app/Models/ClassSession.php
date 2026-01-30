<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClassSession extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'title',
        'type',
        'start_time',
        'duration_minutes',
        'instructor_name',
        'capacity',
    ];

    protected $casts = [
        'start_time' => 'datetime',
    ];

    /**
     * Get all bookings for this class.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'class_id');
    }

    /**
     * Get all users who booked this class.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookings', 'class_id', 'user_id')
                    ->withPivot('booked_at');
    }

    /**
     * Check if the class is full.
     */
    public function isFull(): bool
    {
        return $this->bookings()->count() >= $this->capacity;
    }

    /**
     * Get the current booking count.
     */
    public function getBookedCountAttribute(): int
    {
        return $this->bookings()->count();
    }

    /**
     * Check if a user has booked this class.
     */
    public function isBookedByUser(?User $user): bool
    {
        if (!$user) return false;
        return $this->bookings()->where('user_id', $user->id)->exists();
    }
}
