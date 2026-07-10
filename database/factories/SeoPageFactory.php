<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SeoPage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SeoPage>
 */
final class SeoPageFactory extends Factory
{
    protected $model = SeoPage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => Str::ucfirst($title),
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 1000000),
            'excerpt' => fake()->sentence(),
            'content' => '<p>' . fake()->paragraph() . '</p>',
            'is_featured' => false,
            'is_published' => false,
            'published_at' => null,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => [
            'is_featured' => true,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}
