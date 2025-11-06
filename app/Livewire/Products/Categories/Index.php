<?php

declare(strict_types=1);

namespace App\Livewire\Products\Categories;

use Livewire\Component;

final class Index extends Component
{
    public function render()
    {
        $breadcrumbs = [
            ['name' => 'Kötőelemek', 'url' => '#'],
            ['name' => 'Belső kulcsnyílású hernyócsavarok', 'url' => '#'],
        ];

        return view('livewire.products.categories.index', [
            'breadcrumbs' => $breadcrumbs,
        ])->layout('components.layouts.app');
    }
}
