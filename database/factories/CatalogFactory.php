<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Catalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Catalog>
 */
final class CatalogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(['SKF', 'LOCTITE', 'TENTE', 'SEEGER']) . ' katalógus',
            'description' => fake()->boolean() ? fake()->sentence() : null,
            'file' => 'catalogs/' . fake()->uuid() . '.pdf',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
