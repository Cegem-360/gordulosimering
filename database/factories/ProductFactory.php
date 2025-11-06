<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
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
        $purchasePrice = $this->faker->randomFloat(2, 100, 10000);
        $profitMargin = $this->faker->randomFloat(2, 10, 60);
        $netSellingPrice = $purchasePrice * (1 + $profitMargin / 100);
        $grossSellingPrice = $netSellingPrice * 1.27;

        return [
            // Basic product information
            'group_code' => $this->faker->optional()->numerify('##'),
            'product_code' => $this->faker->unique()->bothify('???-#####'),
            'is_service' => $this->faker->boolean(10),
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->unique()->slug(),
            'catalog_number' => $this->faker->optional()->bothify('CAT-####'),
            'type' => $this->faker->optional()->randomElement(['Standard', 'Premium', 'Economy']),
            'size' => $this->faker->optional()->randomElement(['S', 'M', 'L', 'XL', '10x20', '20x30']),
            'weight' => $this->faker->optional()->randomFloat(3, 0.1, 100),

            // Quality and classification
            'rating' => $this->faker->optional()->randomElement(['A', 'B', 'C']),
            'quality' => $this->faker->optional()->randomElement(['Premium', 'Standard', 'Economy']),
            'product_variety' => $this->faker->optional()->word(),
            'trade_type' => $this->faker->optional()->randomElement(['L', 'K']),
            'usage_type' => $this->faker->optional()->word(),

            // Currency and pricing
            'currency_settlement' => $this->faker->optional()->word(),
            'discount_group' => $this->faker->optional()->randomElement(['A', 'B', 'C']),
            'is_on_sale' => $this->faker->boolean(20),
            'sale_percentage' => $this->faker->optional()->randomFloat(2, 0, 50),
            'pricing' => $this->faker->randomElement(['S', 'F']),
            'list_price' => $this->faker->randomFloat(2, 100, 20000),
            'list_discount' => $this->faker->randomFloat(2, 0, 10),
            'purchase_currency_price' => $purchasePrice,
            'currency' => 'HUF',
            'currency_multiplier' => 1,
            'purchase_price' => $purchasePrice,
            'profit_margin' => $profitMargin,
            'net_selling_price' => round($netSellingPrice, 2),
            'vat_class' => $this->faker->randomElement(['AFA27', 'AFA5', 'AFA0']),
            'gross_selling_price' => round($grossSellingPrice, 2),

            // Stock and units
            'quantity_unit' => $this->faker->randomElement(['db', 'kg', 'm', 'l']),
            'secondary_unit' => $this->faker->optional()->randomElement(['csomag', 'raklap']),
            'minimum_stock' => $this->faker->numberBetween(0, 10),
            'maximum_stock' => $this->faker->numberBetween(50, 500),
            'buffer_stock' => $this->faker->numberBetween(5, 20),
            'order_unit' => $this->faker->numberBetween(1, 10),

            // Official codes
            'ksh_prefix' => $this->faker->optional()->bothify('VTSZ'),
            'ksh_number' => $this->faker->optional()->numerify('####'),

            // Supplier and notes
            'supplier' => $this->faker->optional()->company(),
            'short_note' => $this->faker->optional()->sentence(5),
            'description' => $this->faker->optional()->paragraph(),

            // Barcodes
            'barcode' => $this->faker->optional()->ean13(),
            'ean_code' => $this->faker->optional()->ean13(),

            // Order quantities
            'min_order_quantity' => $this->faker->numberBetween(1, 5),
            'trade_quantity' => $this->faker->optional()->numberBetween(10, 100),
            'pallet_quantity' => $this->faker->optional()->numberBetween(50, 500),

            // Custom fields and images
            'custom_fields' => $this->faker->optional()->passthrough([
                'color' => $this->faker->safeColorName(),
                'material' => $this->faker->word(),
            ]),
            'images' => $this->faker->optional()->passthrough([
                $this->faker->imageUrl(640, 480, 'products'),
                $this->faker->imageUrl(640, 480, 'products'),
            ]),
        ];
    }
}
