<?php

namespace Database\Factories;

use App\Models\MembershipPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MembershipPackage>
 */
class MembershipPackageFactory extends Factory
{
    protected $model = MembershipPackage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Monthly', 'Quarterly', 'Annual', '10 Classes']),
            'description' => fake()->sentence(),
            'duration_type' => 'months',
            'duration_value' => 1,
            'price' => fake()->randomElement([1500, 2000, 2500, 5000]),
            'age_group' => 'All',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Create a monthly package.
     */
    public function monthly(int $price = 2000): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Monthly Membership',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price' => $price,
        ]);
    }

    /**
     * Create a quarterly package.
     */
    public function quarterly(int $price = 5500): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Quarterly Membership',
            'duration_type' => 'months',
            'duration_value' => 3,
            'price' => $price,
        ]);
    }

    /**
     * Create an annual package.
     */
    public function annual(int $price = 20000): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Annual Membership',
            'duration_type' => 'years',
            'duration_value' => 1,
            'price' => $price,
        ]);
    }

    /**
     * Create a class-based package.
     */
    public function classBased(int $classes = 10, int $price = 2500): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $classes . ' Classes Package',
            'duration_type' => 'classes',
            'duration_value' => $classes,
            'price' => $price,
        ]);
    }

    /**
     * Create a package for adults only.
     */
    public function forAdults(): static
    {
        return $this->state(fn (array $attributes) => [
            'age_group' => 'Adults',
        ]);
    }

    /**
     * Create a package for kids only.
     */
    public function forKids(): static
    {
        return $this->state(fn (array $attributes) => [
            'age_group' => 'Kids',
        ]);
    }

    /**
     * Create an inactive package.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
