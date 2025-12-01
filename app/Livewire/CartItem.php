<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

final class CartItem extends Component
{
    public $product;

    public int $quantity;

    public function decreaseQuantity(): void
    {
        if ($this->quantity > $this->product->min_order_quantity) {
            $this->quantity--;
        }
    }

    public function increaseQuantity(): void
    {
        $this->quantity++;
    }

    public function removeProduct(): void
    {
        $sessionCart = Session::get('cart', []);
        $cart = Cart::whereSessionId(Session::getId())->first();
        $cartItem = $cart->items()->get();
        $cartItem->delete();
        $sessionCart = array_filter($sessionCart, fn ($item) => $item['product_id'] !== $this->product->id);
        Session::put('cart', $sessionCart);
        $this->dispatch('removeCartItem');
    }

    public function mount(int $product, int $quantity)
    {
        $this->product = Product::findOrFail($product);
        $this->quantity = $quantity;
    }

    public function render()
    {
        return view('livewire.cart-item');
    }
}
