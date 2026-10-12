<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Imports\CategoryImporter;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Support\TabAwareImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            TabAwareImportAction::make()
                ->importer(CategoryImporter::class)
                ->label('Importálás (CSV)')
                ->color('gray'),
            CreateAction::make(),
        ];
    }
}
