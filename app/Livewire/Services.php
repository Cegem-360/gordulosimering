<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

final class Services extends Component
{
    public function render()
    {
        return view('pages.services')->title('Szolgáltatásaink - Gördülő Simering Kft.');
    }
}
