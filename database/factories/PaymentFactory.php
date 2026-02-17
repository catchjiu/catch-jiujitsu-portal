<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => fake()->randomElement([1500, 2000, 2500, 3000]),
            'month' => now()->format('F Y'),
            'status' => 'Pending Verification',
            'proof_image_path' => null,
            'submitted_at' => now(),
            'payment_method' => fake()->randomElement(['bank', 'linepay']),
            'payment_date' => now()->subDays(rand(0, 3)),
            'account_last_5' => fake()->numerify('#####'),
        ];
    }

    /**
     * Create a payment for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a pending payment.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Pending Verification',
        ]);
    }

    /**
     * Create a paid/approved payment.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Paid',
        ]);
    }

    /**
     * Create a rejected payment.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Rejected',
        ]);
    }

    /**
     * Create an overdue payment.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Overdue',
        ]);
    }

    /**
     * Create a bank transfer payment.
     */
    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'bank',
            'account_last_5' => fake()->numerify('#####'),
        ]);
    }

    /**
     * Create a LINE Pay payment.
     */
    public function linePay(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'linepay',
            'account_last_5' => null,
        ]);
    }
}
