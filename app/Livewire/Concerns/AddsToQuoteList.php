<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Services\QuoteList;
use Filament\Notifications\Notification;
use Livewire\Attributes\Computed;

/**
 * The "Ajánlatkérés" button on the product card and the product page: puts
 * the component's product on the visitor's quote list.
 */
trait AddsToQuoteList
{
    public function addToQuote(QuoteList $quoteList): void
    {
        $quoteList->add($this->product);
        unset($this->isInQuoteList);

        $this->dispatch('quoteListUpdated');

        Notification::make()
            ->title('A termék bekerült az ajánlatkérésbe.')
            ->body('Az ajánlatkérést a fejlécben, a kosár mellett küldheti el.')
            ->success()
            ->send();
    }

    #[Computed]
    public function isInQuoteList(): bool
    {
        return resolve(QuoteList::class)->has($this->product->id);
    }
}
