<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DiscountGroup;
use App\Models\User;
use App\Models\UserDiscount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserDiscount>
 */
final class UserDiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'discount_group_id' => DiscountGroup::factory(),
            'percentage' => fake()->numberBetween(1, 50),
        ];
    }
}
