<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class PrivacyPolicy extends Component
{
    public function render(): Factory|View
    {
        return view('livewire.pages.privacy-policy');
    }
}
