<?php

namespace Database\Factories;

use App\Models\ClassSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassSession>
 */
class ClassSessionFactory extends Factory
{
    protected $model = ClassSession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(['Morning BJJ', 'Evening Gi', 'No-Gi Training', 'Open Mat', 'Fundamentals']),
            'type' => fake()->randomElement(['Gi', 'No-Gi', 'Open Mat', 'Fundamentals']),
            'age_group' => 'Adults',
            'start_time' => fake()->dateTimeBetween('now', '+1 week'),
            'duration_minutes' => fake()->randomElement([60, 90, 120]),
            'instructor_id' => null,
            'instructor_name' => fake()->name(),
            'capacity' => 20,
            'is_cancelled' => false,
        ];
    }

    /**
     * Create a class with a specific instructor.
     */
    public function withInstructor(User $instructor): static
    {
        return $this->state(fn (array $attributes) => [
            'instructor_id' => $instructor->id,
            'instructor_name' => $instructor->name,
        ]);
    }

    /**
     * Create a kids class.
     */
    public function forKids(): static
    {
        return $this->state(fn (array $attributes) => [
            'age_group' => 'Kids',
            'title' => 'Kids BJJ',
        ]);
    }

    /**
     * Create an adults class.
     */
    public function forAdults(): static
    {
        return $this->state(fn (array $attributes) => [
            'age_group' => 'Adults',
        ]);
    }

    /**
     * Create a class for all ages.
     */
    public function forAllAges(): static
    {
        return $this->state(fn (array $attributes) => [
            'age_group' => 'All',
        ]);
    }

    /**
     * Create a cancelled class.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_cancelled' => true,
        ]);
    }

    /**
     * Create a class with limited capacity.
     */
    public function withCapacity(int $capacity): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => $capacity,
        ]);
    }

    /**
     * Create a class at a specific time.
     */
    public function at(\DateTimeInterface $time): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => $time,
        ]);
    }

    /**
     * Create a class in the past.
     */
    public function inPast(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => fake()->dateTimeBetween('-1 week', '-1 hour'),
        ]);
    }

    /**
     * Create a class today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => now()->setTime(rand(6, 20), 0),
        ]);
    }
}
