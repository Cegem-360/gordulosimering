<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

final class ShippingMethodSeeder extends Seeder
{
    /**
     * @var array<int, array{max_weight: int, bank_transfer: int, cash_on_delivery: int}>
     */
    private const array GLS_RATES = [
        ['max_weight' => 3, 'bank_transfer' => 2550, 'cash_on_delivery' => 3550],
        ['max_weight' => 15, 'bank_transfer' => 2950, 'cash_on_delivery' => 3950],
        ['max_weight' => 30, 'bank_transfer' => 3750, 'cash_on_delivery' => 4750],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ShippingMethod::query()->create([
            'name' => 'gls',
            'title' => 'GLS futárszolgálat',
            'slug' => 'gls-futarszolgalat',
            'description' => 'Házhoz szállítás GLS futárszolgálattal, általában 1-2 munkanapon belül.',
            'cost' => 1490,
            'rates' => self::GLS_RATES,
        ]);

        ShippingMethod::query()->create([
            'name' => 'gls-parcel-point',
            'title' => 'GLS csomagpont / csomagautomata',
            'slug' => 'gls-csomagpont',
            'description' => 'Átvétel a kiválasztott GLS csomagponton vagy GLS csomagautomatában, általában 1-2 munkanapon belül.',
            'cost' => 0,
            'rates' => self::GLS_RATES,
            'requires_parcel_point' => true,
        ]);

        ShippingMethod::query()->create([
            'name' => 'foxpost',
            'title' => 'Foxpost csomagautomata',
            'slug' => 'foxpost-csomagautomata',
            'description' => 'Átvétel a kiválasztott Foxpost csomagautomatánál, általában 1-3 munkanapon belül.',
            'cost' => 990,
        ]);
    }
}
