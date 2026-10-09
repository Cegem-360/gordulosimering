<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\QuoteList;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Header link to the Ajánlatkérés page, beside the cart, with the number of
 * products on the visitor's quote list.
 */
final class QuoteListIcon extends Component
{
    public int $itemCount = 0;

    public function mount(QuoteList $quoteList): void
    {
        $this->itemCount = $quoteList->count();
    }

    #[On('quoteListUpdated')]
    public function refreshCount(QuoteList $quoteList): void
    {
        $this->itemCount = $quoteList->count();
    }

    public function render(): View
    {
        return view('livewire.quote-list-icon');
    }
}
