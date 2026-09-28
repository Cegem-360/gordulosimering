<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
final class OrderFactory extends Factory
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
            'shipping_method_id' => ShippingMethod::factory(),
            'payment_method' => 'bacs',
            'payment_method_title' => 'Banki átutalás',
            'set_paid' => false,
            'billing_name' => fake()->name(),
            'billing_email' => fake()->safeEmail(),
            'billing_country' => 'Magyarország',
            'order_status' => OrderStatus::PENDING,
            'order_currency' => 'HUF',
            'shipping_cost' => 0,
        ];
    }
}
