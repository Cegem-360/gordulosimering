<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

final class Cart extends Component
{
    public array $cartItems = [];

    public function mount(): void
    {
        // Load first 3 products from database as demo cart items
        $products = Product::query()
            ->whereNotNull('net_selling_price')
            ->where('net_selling_price', '>', 0)
            ->take(3)
            ->get();

        foreach ($products as $product) {
            $this->cartItems[] = [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name ?? $product->product_code,
                'product_code' => $product->product_code,
                'image' => $product->images[0] ?? null,
                'net_price' => $product->net_selling_price,
                'gross_price' => $product->gross_selling_price,
                'quantity' => rand(1, 5),
                'min_order_quantity' => $product->min_order_quantity ?? 1,
                'quantity_unit' => $product->quantity_unit ?? 'db',
                'in_stock' => ($product->minimum_stock ?? 0) > 0,
            ];
        }
    }

    public function updateQuantity(int $index, int $quantity): void
    {
        if (isset($this->cartItems[$index])) {
            $minQty = $this->cartItems[$index]['min_order_quantity'];
            $this->cartItems[$index]['quantity'] = max($minQty, $quantity);
        }
    }

    public function removeItem(int $index): void
    {
        if (isset($this->cartItems[$index])) {
            unset($this->cartItems[$index]);
            $this->cartItems = array_values($this->cartItems);
        }
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->cartItems)->sum(fn ($item) => $item['net_price'] * $item['quantity']);
    }

    public function getVatAmountProperty(): float
    {
        return $this->subtotal * 0.27;
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->vatAmount;
    }

    public function getItemCountProperty(): int
    {
        return collect($this->cartItems)->sum('quantity');
    }

    public function render()
    {
        return view('livewire.cart')->title('Kosár - Gördülő Simering Kft.');
    }
}
