<?php

declare(strict_types=1);

use App\Livewire\LiveSearch;
use App\Models\Product;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(LiveSearch::class)
        ->assertStatus(200);
});

it('does not search with less than 2 characters', function () {
    Livewire::test(LiveSearch::class)
        ->set('query', 'a')
        ->assertSet('showResults', false);
});

it('shows results when query has 2 or more characters', function () {
    Product::factory()->create([
        'name' => 'Test Product',
        'product_code' => 'TEST123',
    ]);

    Livewire::test(LiveSearch::class)
        ->set('query', 'TEST')
        ->assertSet('showResults', true);
});

it('finds products by product code', function () {
    $product = Product::factory()->create([
        'name' => 'Some Product',
        'product_code' => 'ABC123',
    ]);

    $component = Livewire::test(LiveSearch::class)
        ->set('query', 'ABC');

    expect($component->get('results'))->toHaveCount(1);
    expect($component->get('results')->first()->product_code)->toBe('ABC123');
});

it('finds products by name', function () {
    $product = Product::factory()->create([
        'name' => 'Golyóscsapágy 6205',
        'product_code' => 'XYZ999',
    ]);

    $component = Livewire::test(LiveSearch::class)
        ->set('query', 'Golyós');

    expect($component->get('results'))->toHaveCount(1);
    expect($component->get('results')->first()->name)->toBe('Golyóscsapágy 6205');
});
