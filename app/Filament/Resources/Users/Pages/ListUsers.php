<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Imports\UserImporter;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\TabAwareImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            TabAwareImportAction::make()
                ->importer(UserImporter::class)
                ->label('Importálás (CSV)')
                ->color('gray'),
            CreateAction::make(),
        ];
    }
}
