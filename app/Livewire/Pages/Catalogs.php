<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Catalog;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * The Katalógusok page: the catalogues uploaded in the admin.
 */
final class Catalogs extends Component
{
    public function render(): Factory|View
    {
        return view('livewire.pages.catalogs', [
            'catalogs' => Catalog::query()->listed()->get(),
        ]);
    }
}
