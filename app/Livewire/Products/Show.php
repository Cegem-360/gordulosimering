<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Models\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class Show extends Component
{
    public Product $product;

    public function mount(Product $product): void {}

    public function render(): Factory|View
    {
        return view('livewire.products.show');
    }
}
