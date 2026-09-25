<?php

declare(strict_types=1);

use App\Models\Product;

function productPriced(float|string|null $netPrice, bool $isOnSale, float|string|null $percentage): Product
{
    return new Product([
        'net_selling_price' => $netPrice,
        'is_on_sale' => $isOnSale,
        'sale_percentage' => $percentage,
    ]);
}

it('uses the ERP percentage for a product on sale', function (): void {
    $product = productPriced(1000, true, 30);

    expect($product->isOnSale())->toBeTrue()
        ->and($product->effective_sale_percentage)->toBe(30.0)
        ->and($product->sale_price)->toBe(700)
        ->and($product->unit_price)->toBe(700.0);
});

it('gives 10% to a product on sale without a percentage', function (float|string|null $percentage): void {
    $product = productPriced(1000, true, $percentage);

    expect($product->effective_sale_percentage)->toBe(10.0)
        ->and($product->sale_price)->toBe(900)
        ->and($product->unit_price)->toBe(900.0);
})->with([
    'zero' => [0],
    'zero as ERP decimal' => ['0.00'],
    'empty' => [null],
]);

it('charges the full price when the product is not on sale', function (): void {
    $product = productPriced(1000, false, 30);

    expect($product->isOnSale())->toBeFalse()
        ->and($product->effective_sale_percentage)->toBe(0.0)
        ->and($product->sale_price)->toBeNull()
        ->and($product->unit_price)->toBe(1000.0);
});

it('rounds the sale price to whole forints', function (float $netPrice, float $percentage, int $expected): void {
    expect(productPriced($netPrice, true, $percentage)->sale_price)->toBe($expected);
})->with([
    '999 Ft, 52%' => [999, 52, 480],
    '999.50 Ft, 30%' => [999.50, 30, 700],
    '15 Ft, 55%' => [15, 55, 7],
    'exactly half a forint, 45 Ft, 30%' => [45, 30, 32],
    'exactly half a forint, 85 Ft, 30%' => [85, 30, 60],
]);

it('never goes below zero for a discount of 100% or more', function (float $percentage): void {
    $product = productPriced(1000, true, $percentage);

    expect($product->effective_sale_percentage)->toBe(100.0)
        ->and($product->sale_price)->toBe(0);
})->with([100, 150]);

it('handles a product on sale without a price', function (float|string|null $netPrice): void {
    $product = productPriced($netPrice, true, 30);

    expect($product->sale_price)->toBe(0)
        ->and($product->unit_price)->toBe(0.0);
})->with([
    'null' => [null],
    'zero' => [0],
]);
