<?php

namespace Database\Factories;

use App\Models\MembershipPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'chinese_name' => null,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'rank' => 'White',
            'belt_color' => null,
            'stripes' => 0,
            'mat_hours' => 0,
            'is_admin' => false,
            'is_coach' => false,
            'discount_type' => 'none',
            'discount_amount' => 0,
            'avatar_url' => null,
            'monthly_class_goal' => 12,
            'monthly_hours_goal' => 15,
            'reminders_enabled' => true,
            'public_profile' => false,
            'line_id' => null,
            'gender' => fake()->randomElement(['male', 'female']),
            'age_group' => 'Adults',
            'dob' => fake()->date(),
            'membership_package_id' => null,
            'membership_status' => 'none',
            'membership_expires_at' => null,
            'classes_remaining' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }

    /**
     * Create a coach user.
     */
    public function coach(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_coach' => true,
        ]);
    }

    /**
     * Create a member with an active membership.
     */
    public function withActiveMembership(?MembershipPackage $package = null): static
    {
        return $this->state(function (array $attributes) use ($package) {
            $data = [
                'membership_status' => 'active',
                'membership_expires_at' => now()->addMonth(),
            ];

            if ($package) {
                $data['membership_package_id'] = $package->id;
                if ($package->duration_type === 'classes') {
                    $data['classes_remaining'] = $package->duration_value;
                }
            }

            return $data;
        });
    }

    /**
     * Create a member with an expired membership.
     */
    public function withExpiredMembership(): static
    {
        return $this->state(fn (array $attributes) => [
            'membership_status' => 'expired',
            'membership_expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Create a gratis (free) member.
     */
    public function gratis(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'gratis',
            'membership_status' => 'active',
        ]);
    }

    /**
     * Create a member with a fixed discount.
     */
    public function withDiscount(int $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'fixed',
            'discount_amount' => $amount,
        ]);
    }

    /**
     * Create a kids member.
     */
    public function kids(): static
    {
        return $this->state(fn (array $attributes) => [
            'age_group' => 'Kids',
            'dob' => fake()->dateTimeBetween('-15 years', '-5 years'),
        ]);
    }

    /**
     * Create a member with class-based package.
     */
    public function withClassesRemaining(int $classes): static
    {
        return $this->state(fn (array $attributes) => [
            'membership_status' => 'active',
            'classes_remaining' => $classes,
        ]);
    }
}
