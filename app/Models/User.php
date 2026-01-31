<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

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
        'is_coach',
        'discount_type',
        'discount_amount',
        'avatar_url',
        'monthly_class_goal',
        'monthly_hours_goal',
        'reminders_enabled',
        'public_profile',
        'line_id',
        'gender',
        'age_group',
        'dob',
        'membership_package_id',
        'membership_status',
        'membership_expires_at',
        'classes_remaining',
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
            'is_coach' => 'boolean',
            'stripes' => 'integer',
            'mat_hours' => 'integer',
            'monthly_class_goal' => 'integer',
            'monthly_hours_goal' => 'integer',
            'reminders_enabled' => 'boolean',
            'public_profile' => 'boolean',
            'dob' => 'date',
            'membership_expires_at' => 'date',
            'classes_remaining' => 'integer',
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
     * Get the user's membership package.
     */
    public function membershipPackage(): BelongsTo
    {
        return $this->belongsTo(MembershipPackage::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Check if the user is a coach.
     */
    public function isCoach(): bool
    {
        return $this->is_coach ?? false;
    }

    /**
     * Check if the user has an active membership that allows booking.
     */
    public function hasActiveMembership(): bool
    {
        // Gratis members always have access
        if ($this->discount_type === 'gratis') {
            return true;
        }

        // Check membership status
        if ($this->membership_status !== 'active') {
            return false;
        }

        // Check if membership has expired
        if ($this->membership_expires_at && Carbon::parse($this->membership_expires_at)->isPast()) {
            return false;
        }

        // For class-based packages, check if classes remain
        if ($this->membershipPackage && $this->membershipPackage->duration_type === 'classes') {
            if ($this->classes_remaining !== null && $this->classes_remaining <= 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the user has gratis membership.
     */
    public function isGratis(): bool
    {
        return $this->discount_type === 'gratis';
    }

    /**
     * Check if member has a fixed discount.
     */
    public function hasFixedDiscount(): bool
    {
        return $this->discount_type === 'fixed' && ($this->discount_amount ?? 0) > 0;
    }

    /**
     * Get the actual discount amount value.
     */
    public function getDiscountAmountValue(): int
    {
        if ($this->discount_type === 'gratis') {
            return 0; // Free, no amount needed
        }
        return (int) ($this->discount_amount ?? 0);
    }

    /**
     * Get the reason why membership is not active.
     */
    public function getMembershipIssueAttribute(): ?string
    {
        // Gratis members never have issues
        if ($this->discount_type === 'gratis') {
            return null;
        }

        if ($this->membership_status === 'none') {
            return 'No active membership. Please purchase a membership package.';
        }

        if ($this->membership_status === 'pending') {
            return 'Your membership payment is pending verification.';
        }

        if ($this->membership_status === 'expired') {
            return 'Your membership has expired. Please renew to continue booking.';
        }

        if ($this->membership_expires_at && Carbon::parse($this->membership_expires_at)->isPast()) {
            return 'Your membership has expired. Please renew to continue booking.';
        }

        if ($this->membershipPackage && $this->membershipPackage->duration_type === 'classes') {
            if ($this->classes_remaining !== null && $this->classes_remaining <= 0) {
                return 'You have no classes remaining. Please purchase more classes.';
            }
        }

        return null;
    }

    /**
     * Decrement classes remaining (for class-based packages).
     */
    public function decrementClassesRemaining(): void
    {
        if ($this->classes_remaining !== null && $this->classes_remaining > 0) {
            $this->decrement('classes_remaining');
        }
    }

    /**
     * Increment classes remaining (when cancelling a booking).
     */
    public function incrementClassesRemaining(): void
    {
        if ($this->membershipPackage && $this->membershipPackage->duration_type === 'classes') {
            $this->increment('classes_remaining');
        }
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
