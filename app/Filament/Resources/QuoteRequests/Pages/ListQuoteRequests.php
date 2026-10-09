<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuoteRequests\Pages;

use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use Filament\Resources\Pages\ListRecords;

final class ListQuoteRequests extends ListRecords
{
    protected static string $resource = QuoteRequestResource::class;
}
