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
        'name',
        'email',
        'password',
        'rank',
        'stripes',
        'mat_hours',
        'is_admin',
        'avatar_url',
        'monthly_class_goal',
        'monthly_hours_goal',
        'reminders_enabled',
        'public_profile',
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
