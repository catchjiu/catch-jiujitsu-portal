<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClassSession extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'title',
        'title_zh',
        'type',
        'age_group',
        'start_time',
        'duration_minutes',
        'instructor_id',
        'instructor_name', // Kept for backward compatibility
        'capacity',
        'is_cancelled',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'is_cancelled' => 'boolean',
    ];

    /**
     * Get the localized title based on current locale.
     */
    public function getLocalizedTitleAttribute(): string
    {
        if (app()->getLocale() === 'zh-TW' && $this->title_zh) {
            return $this->title_zh;
        }
        return $this->title;
    }

    /**
     * Get the instructor (coach) for this class.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get instructor name (from relationship or legacy field).
     */
    public function getInstructorDisplayNameAttribute(): string
    {
        if ($this->instructor) {
            return $this->instructor->name;
        }
        return $this->instructor_name ?? 'TBA';
    }

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
