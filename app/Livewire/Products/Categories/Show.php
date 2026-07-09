<?php

declare(strict_types=1);

namespace App\Livewire\Products\Categories;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class Show extends Component
{
    public function render(): Factory|View
    {
        return view('livewire.products.categories.show');
    }
}
