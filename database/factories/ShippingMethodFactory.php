<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingMethod>
 */
final class ShippingMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'cost' => fake()->numberBetween(500, 3000),
        ];
    }

    /**
     * The GLS net rates by weight band.
     */
    public function glsRates(): static
    {
        return $this->state(fn (array $attributes): array => [
            'rates' => [
                ['max_weight' => 3, 'bank_transfer' => 2550, 'cash_on_delivery' => 3550],
                ['max_weight' => 15, 'bank_transfer' => 2950, 'cash_on_delivery' => 3950],
                ['max_weight' => 30, 'bank_transfer' => 3750, 'cash_on_delivery' => 4750],
            ],
        ]);
    }

    /**
     * Delivered to a GLS parcel shop or locker picked on the map.
     */
    public function parcelPoint(): static
    {
        return $this->glsRates()->state(fn (array $attributes): array => [
            'requires_parcel_point' => true,
        ]);
    }
}
