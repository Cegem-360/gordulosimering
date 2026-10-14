<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\RecommendationRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecommendationRule>
 */
final class RecommendationRuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_category_id' => Category::factory(),
            'target_category_id' => Category::factory(),
            'target_code_prefix' => null,
            'is_active' => true,
        ];
    }

    public function forEverything(): self
    {
        return $this->state(['source_category_id' => null]);
    }

    public function codePrefix(string $prefix): self
    {
        return $this->state(['target_category_id' => null, 'target_code_prefix' => $prefix]);
    }
}
