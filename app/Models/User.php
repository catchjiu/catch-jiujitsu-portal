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
        'belt_variation',
        'belt_color',
        'stripes',
        'mat_hours',
        'hours_this_year',
        'is_admin',
        'is_coach',
        'accepting_private_classes',
        'private_class_price',
        'discount_type',
        'discount_amount',
        'avatar_url',
        'monthly_class_goal',
        'monthly_hours_goal',
        'reminders_enabled',
        'public_profile',
        'locale',
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
            'accepting_private_classes' => 'boolean',
            'private_class_price' => 'decimal:2',
            'stripes' => 'integer',
            'mat_hours' => 'integer',
            'hours_this_year' => 'integer',
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
     * Get the user's family membership (if any).
     */
    public function familyMember(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FamilyMember::class);
    }

    /**
     * Get the user's family (if they belong to one).
     */
    public function family(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(Family::class, FamilyMember::class, 'user_id', 'id', 'id', 'family_id');
    }

    /**
     * Check if the user belongs to a family.
     */
    public function isInFamily(): bool
    {
        return $this->familyMember()->exists();
    }

    /**
     * Get the family's other members (excluding this user).
     */
    public function familyMembers(): \Illuminate\Support\Collection
    {
        $fm = $this->familyMember;
        if (!$fm || !$fm->family) {
            return collect();
        }
        return $fm->family->members()->with('user')->get()->map(fn ($m) => $m->user);
    }

    /**
     * Get all users in this user's family (including self).
     */
    public function familyMembersWithSelf(): \Illuminate\Support\Collection
    {
        $fm = $this->familyMember;
        if (!$fm || !$fm->family) {
            return collect([$this]);
        }
        return $fm->family->members()->with('user')->get()->map(fn ($m) => $m->user);
    }

    /**
     * Get the "current" user for family context (viewing member from session, or self).
     * Only returns a user from the same family; otherwise returns auth user.
     */
    public static function currentFamilyMember(): ?User
    {
        $me = \Illuminate\Support\Facades\Auth::user();
        if (!$me || !$me->isInFamily()) {
            return $me;
        }
        $viewingId = session('viewing_family_user_id');
        if (!$viewingId) {
            return $me;
        }
        $viewing = User::find($viewingId);
        if (!$viewing || !$viewing->familyMember || $viewing->familyMember->family_id !== $me->familyMember->family_id) {
            return $me;
        }
        return $viewing;
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
     * Get the user's shop orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    /**
     * Get the user's membership package.
     */
    public function membershipPackage(): BelongsTo
    {
        return $this->belongsTo(MembershipPackage::class);
    }

    /**
     * Coach availability slots (recurring weekly).
     */
    public function coachAvailability(): HasMany
    {
        return $this->hasMany(CoachAvailability::class, 'user_id');
    }

    /**
     * Private class bookings where this user is the coach.
     */
    public function privateClassBookingsAsCoach(): HasMany
    {
        return $this->hasMany(PrivateClassBooking::class, 'coach_id');
    }

    /**
     * Private class bookings where this user is the member.
     */
    public function privateClassBookingsAsMember(): HasMany
    {
        return $this->hasMany(PrivateClassBooking::class, 'member_id');
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
     * Get BJJ age category from date of birth (Kid 1–6, Juvenile, Adult 18, Master 30/36/41/46/51/56).
     * Ranges shift by one year each calendar year per config.
     */
    public function getBjjAgeCategoryAttribute(): ?string
    {
        if (!$this->dob) {
            return null;
        }
        return \App\Services\BjjAgeCategoryService::getCategory((int) $this->dob->format('Y'));
    }

    /**
     * Get the user's next booked class.
     */
    public function nextBookedClass(): ?ClassSession
    {
        return $this->bookedClasses()
                    ->with('instructor')
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

    /**
     * Get calculated mat hours (total hours from all past attended classes).
     */
    public function getCalculatedMatHoursAttribute(): int
    {
        $totalMinutes = $this->bookings()
            ->whereHas('classSession', function($query) {
                $query->where('start_time', '<', now()); // Only count past classes
            })
            ->with('classSession')
            ->get()
            ->sum(function($booking) {
                return $booking->classSession->duration_minutes ?? 0;
            });

        return (int) round($totalMinutes / 60);
    }

    /**
     * Get total hours trained this year (from past attended classes).
     */
    public function getHoursThisYearAttribute(): float
    {
        $totalMinutes = $this->bookings()
            ->whereHas('classSession', function ($query) {
                $query->whereYear('start_time', now()->year)
                    ->where('start_time', '<', now());
            })
            ->with('classSession')
            ->get()
            ->sum(function ($booking) {
                return $booking->classSession->duration_minutes ?? 0;
            });

        return round($totalMinutes / 60, 1);
    }
}
