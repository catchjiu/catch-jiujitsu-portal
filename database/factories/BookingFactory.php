<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'class_id' => ClassSession::factory(),
            'booked_at' => now(),
            'checked_in' => false,
        ];
    }

    /**
     * Create a booking for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a booking for a specific class.
     */
    public function forClass(ClassSession $class): static
    {
        return $this->state(fn (array $attributes) => [
            'class_id' => $class->id,
        ]);
    }

    /**
     * Create a checked-in booking.
     */
    public function checkedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'checked_in' => true,
        ]);
    }
}
