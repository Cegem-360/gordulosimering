<?php

declare(strict_types=1);

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());
});

it('finds products by product code in the admin global search', function (): void {
    Product::factory()->create(['name' => 'Gördülőcsapágy', 'product_code' => 'SKF-6204-2RS']);
    Product::factory()->create(['name' => 'Szimering', 'product_code' => 'NBR-25x40']);

    $results = ProductResource::getGlobalSearchResults('6204');

    expect($results)->toHaveCount(1)
        ->and($results->first()->title)->toBe('Gördülőcsapágy')
        ->and($results->first()->details)->toBe(['Cikkszám' => 'SKF-6204-2RS']);
});

it('still finds products by name in the admin global search', function (): void {
    Product::factory()->create(['name' => 'Gördülőcsapágy', 'product_code' => 'SKF-6204-2RS']);

    expect(ProductResource::getGlobalSearchResults('csapágy'))->toHaveCount(1);
});
