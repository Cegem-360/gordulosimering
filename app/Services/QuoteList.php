<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * The visitor's ajánlatkérés list: products collected from the cards and
 * product pages, sent as one request from the Ajánlatkérés page. Kept in
 * the session as productId => quantity; quantities follow the product's
 * minimum and order unit, like the cart.
 */
final class QuoteList
{
    private const string SESSION_KEY = 'quote_list';

    /**
     * @return array<int, int>
     */
    public function items(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function add(Product $product): void
    {
        $items = $this->items();
        $items[$product->id] = $product->orderableQuantity(($items[$product->id] ?? 0) + $product->minimumOrderQuantity());

        $this->persist($items);
    }

    public function update(Product $product, int $quantity): void
    {
        $items = $this->items();
        $items[$product->id] = $product->orderableQuantity($quantity);

        $this->persist($items);
    }

    public function remove(int $productId): void
    {
        $items = $this->items();
        unset($items[$productId]);

        $this->persist($items);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function has(int $productId): bool
    {
        return array_key_exists($productId, $this->items());
    }

    /**
     * Number of different products, which the header badge shows.
     */
    public function count(): int
    {
        return count($this->items());
    }

    /**
     * The stored products with their quantities, leaving out any that no
     * longer exist.
     *
     * @return Collection<int, array{product: Product, quantity: int}>
     */
    public function lines(): Collection
    {
        $items = $this->items();

        if ($items === []) {
            return collect();
        }

        return Product::query()
            ->whereIn('id', array_keys($items))
            ->get()
            ->sortBy(fn (Product $product): int => array_search($product->id, array_keys($items), true))
            ->map(fn (Product $product): array => ['product' => $product, 'quantity' => $items[$product->id]])
            ->values();
    }

    /**
     * @param  array<int, int>  $items
     */
    private function persist(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
    }
}
