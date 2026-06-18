<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;

it('imports the real category tree from database/data', function () {
    $this->artisan('app:import-categories')
        ->assertSuccessful();

    // 16 főkategória a fájlban
    expect(Category::whereNull('category_id')->count())->toBe(16)
        ->and(Category::where('name', 'CSAPÁGYAK')->whereNull('category_id')->exists())->toBeTrue();
});

it('also links products when --link is passed', function () {
    Product::factory()->create(['name' => 'DURACELL 9V-os elem']);

    $this->artisan('app:import-categories', ['--link' => true])
        ->assertSuccessful();

    expect(Category::has('products')->count())->toBeGreaterThan(0);
});
