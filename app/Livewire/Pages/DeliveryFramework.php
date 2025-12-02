<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class DeliveryFramework extends Component
{
    public function render(): Factory|View
    {
        return view('livewire.pages.delivery-framework');
    }
}
