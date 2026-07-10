<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;

use function Pest\Laravel\artisan;

it('imports the real category tree from database/data', function (): void {
    artisan('app:import-categories')
        ->assertSuccessful();

    // 16 főkategória a fájlban
    expect(Category::query()->whereNull('category_id')->count())->toBe(16)
        ->and(Category::query()->where('name', 'CSAPÁGYAK')->whereNull('category_id')->exists())->toBeTrue();
});

it('also links products when --link is passed', function (): void {
    Product::factory()->create(['name' => 'DURACELL 9V-os elem']);

    artisan('app:import-categories', ['--link' => true])
        ->assertSuccessful();

    expect(Category::query()->has('products')->count())->toBeGreaterThan(0);
});
