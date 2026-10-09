<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\ShippingMethod;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class Services extends Component
{
    public function render(): Factory|View
    {
        return view('livewire.pages.services', [
            'glsRates' => collect(ShippingMethod::query()->where('name', 'gls')->value('rates') ?? [])
                ->sortBy('max_weight')
                ->values()
                ->all(),
        ]);
    }
}
