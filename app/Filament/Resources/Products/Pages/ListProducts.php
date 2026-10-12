<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Support\CsvDelimiterSelect;
use App\Filament\Support\TabAwareImportAction;
use App\Services\ProductCsvExporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exportálás (CSV)')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->schema([CsvDelimiterSelect::make()])
                ->modalSubmitActionLabel('Exportálás')
                ->action(fn (array $data, ProductCsvExporter $exporter): StreamedResponse => $exporter->download(
                    $this->getFilteredTableQuery(),
                    'termekek-' . now()->format('Y-m-d'),
                    $data['delimiter'],
                )),
            TabAwareImportAction::make()
                ->importer(ProductImporter::class)
                ->label('Importálás (CSV)')
                ->color('gray'),
            CreateAction::make(),
        ];
    }
}
