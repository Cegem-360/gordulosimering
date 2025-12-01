<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

final class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ShippingMethod::create([
            'name' => 'gls',
            'title' => 'GLS futárszolgálat',
            'slug' => 'gls-futarszolgalat',
            'description' => 'Házhoz szállítás GLS futárszolgálattal, általában 1-2 munkanapon belül.',
            'cost' => 1490,
        ]);

        ShippingMethod::create([
            'name' => 'foxpost',
            'title' => 'Foxpost csomagautomata',
            'slug' => 'foxpost-csomagautomata',
            'description' => 'Átvétel a kiválasztott Foxpost csomagautomatánál, általában 1-3 munkanapon belül.',
            'cost' => 990,
        ]);
    }
}
