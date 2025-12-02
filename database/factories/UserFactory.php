<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password', // Hashed by model's 'hashed' cast
            'remember_token' => Str::random(10),
            'phone' => null,
            'billing_name' => null,
            'billing_company_name' => null,
            'billing_vat_number' => null,
            'billing_company_office' => null,
            'billing_postcode' => null,
            'billing_city' => null,
            'billing_address_1' => null,
            'billing_address_2' => null,
            'billing_country' => null,
            'billing_state' => null,
            'shipping_name' => null,
            'shipping_postcode' => null,
            'shipping_city' => null,
            'shipping_address_1' => null,
            'shipping_address_2' => null,
            'shipping_country' => null,
            'shipping_state' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }
}
