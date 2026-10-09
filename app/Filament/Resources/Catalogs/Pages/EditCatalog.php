<?php

declare(strict_types=1);

namespace App\Filament\Resources\Catalogs\Pages;

use App\Filament\Resources\Catalogs\CatalogResource;
use Filament\Resources\Pages\EditRecord;

final class EditCatalog extends EditRecord
{
    protected static string $resource = CatalogResource::class;
}
