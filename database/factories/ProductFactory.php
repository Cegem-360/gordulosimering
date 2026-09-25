<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchasePrice = fake()->randomFloat(2, 100, 10000);
        $profitMargin = fake()->randomFloat(2, 10, 60);
        $netSellingPrice = $purchasePrice * (1 + $profitMargin / 100);
        $grossSellingPrice = $netSellingPrice * 1.27;

        return [
            // Basic product information
            'group_code' => fake()->optional()->numerify('##'),
            'product_code' => fake()->unique()->bothify('???-#####'),
            'is_service' => fake()->boolean(10),
            'is_web_visible' => true,
            'is_inactive' => false,
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'catalog_number' => fake()->optional()->bothify('CAT-####'),
            'type' => fake()->optional()->randomElement(['Standard', 'Premium', 'Economy']),
            'size' => fake()->optional()->randomElement(['S', 'M', 'L', 'XL', '10x20', '20x30']),
            'weight' => fake()->optional()->randomFloat(3, 0.1, 100),

            // Quality and classification
            'rating' => fake()->optional()->randomElement(['A', 'B', 'C']),
            'quality' => fake()->optional()->randomElement(['Premium', 'Standard', 'Economy']),
            'product_variety' => fake()->optional()->word(),
            'trade_type' => fake()->optional()->randomElement(['L', 'K']),
            'usage_type' => fake()->optional()->word(),

            // Currency and pricing
            'currency_settlement' => fake()->optional()->word(),
            'discount_group' => fake()->optional()->randomElement(['A', 'B', 'C']),
            'is_on_sale' => false,
            'sale_percentage' => null,
            'pricing' => fake()->randomElement(['S', 'F']),
            'net_selling_price' => round($netSellingPrice, 2),
            'vat_class' => fake()->randomElement(['AFA27', 'AFA5', 'AFA0']),
            'gross_selling_price' => round($grossSellingPrice, 2),

            // Stock and units
            'quantity_unit' => fake()->randomElement(['db', 'kg', 'm', 'l']),
            'secondary_unit' => fake()->optional()->randomElement(['csomag', 'raklap']),
            'minimum_stock' => fake()->numberBetween(0, 10),
            'maximum_stock' => fake()->numberBetween(50, 500),
            'buffer_stock' => fake()->numberBetween(5, 20),
            'order_unit' => fake()->numberBetween(1, 10),

            // Official codes
            'ksh_prefix' => fake()->optional()->bothify('VTSZ'),
            'ksh_number' => fake()->optional()->numerify('####'),

            // Supplier and notes
            'supplier' => fake()->optional()->company(),
            'short_note' => fake()->optional()->sentence(5),
            'description' => fake()->optional()->paragraph(),

            // Barcodes
            'barcode' => fake()->optional()->ean13(),
            'ean_code' => fake()->optional()->ean13(),

            // Order quantities
            'min_order_quantity' => fake()->numberBetween(1, 5),
            'trade_quantity' => fake()->optional()->numberBetween(10, 100),
            'pallet_quantity' => fake()->optional()->numberBetween(50, 500),

            // Custom fields and images
            'custom_fields' => fake()->optional()->passthrough([
                'color' => fake()->safeColorName(),
                'material' => fake()->word(),
            ]),
            'images' => fake()->optional()->passthrough([
                fake()->imageUrl(640, 480, 'products'),
                fake()->imageUrl(640, 480, 'products'),
            ]),
        ];
    }

    /**
     * A webshopban nem megjelenő termék (az ERP-export NEM értéke).
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_web_visible' => false,
        ]);
    }

    /**
     * Akciós termék. A null százalék az ERP üres „Akció %” mezőjét utánozza.
     */
    public function onSale(?float $percentage = 30): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_on_sale' => true,
            'sale_percentage' => $percentage,
        ]);
    }
}
