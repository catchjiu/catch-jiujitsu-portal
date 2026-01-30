<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'chinese_name',
        'email',
        'password',
        'rank',
        'belt_color',
        'stripes',
        'mat_hours',
        'is_admin',
        'avatar_url',
        'monthly_class_goal',
        'monthly_hours_goal',
        'reminders_enabled',
        'public_profile',
        'line_id',
        'gender',
        'age_group',
        'dob',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'stripes' => 'integer',
            'mat_hours' => 'integer',
            'monthly_class_goal' => 'integer',
            'monthly_hours_goal' => 'integer',
            'reminders_enabled' => 'boolean',
            'public_profile' => 'boolean',
            'dob' => 'date',
        ];
    }

    /**
     * Get the user's bookings.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the classes the user has booked.
     */
    public function bookedClasses(): BelongsToMany
    {
        return $this->belongsToMany(ClassSession::class, 'bookings', 'user_id', 'class_id')
                    ->withPivot('booked_at');
    }

    /**
     * Get the user's payments.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Get the user's full name.
     */
    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the user's full name (alias).
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get the avatar URL (handles both external URLs and local storage).
     */
    public function getAvatarAttribute(): ?string
    {
        if (!$this->avatar_url) {
            return null;
        }
        
        // If it's already a full URL, return as-is
        if (str_starts_with($this->avatar_url, 'http')) {
            return $this->avatar_url;
        }
        
        // Otherwise, it's a local storage path
        return asset('storage/' . $this->avatar_url);
    }

    /**
     * Get the user's next booked class.
     */
    public function nextBookedClass(): ?ClassSession
    {
        return $this->bookedClasses()
                    ->where('start_time', '>', now())
                    ->orderBy('start_time')
                    ->first();
    }

    /**
     * Get the number of classes attended this month.
     */
    public function getMonthlyClassesAttendedAttribute(): int
    {
        return $this->bookings()
            ->whereHas('classSession', function($query) {
                $query->whereMonth('start_time', now()->month)
                      ->whereYear('start_time', now()->year)
                      ->where('start_time', '<', now()); // Only count past classes
            })
            ->count();
    }

    /**
     * Get the total hours trained this month.
     */
    public function getMonthlyHoursTrainedAttribute(): float
    {
        $totalMinutes = $this->bookings()
            ->whereHas('classSession', function($query) {
                $query->whereMonth('start_time', now()->month)
                      ->whereYear('start_time', now()->year)
                      ->where('start_time', '<', now()); // Only count past classes
            })
            ->with('classSession')
            ->get()
            ->sum(function($booking) {
                return $booking->classSession->duration_minutes ?? 0;
            });

        return round($totalMinutes / 60, 1);
    }
}
