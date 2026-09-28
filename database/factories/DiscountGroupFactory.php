<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DiscountGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscountGroup>
 */
final class DiscountGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('??#'),
            'name' => null,
        ];
    }
}
