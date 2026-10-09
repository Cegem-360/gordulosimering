<?php

declare(strict_types=1);

namespace App\Filament\Resources\Catalogs\Tables;

use App\Models\Catalog;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

final class CatalogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->label('Cím')
                    ->description(fn (Catalog $record): ?string => $record->description)
                    ->url(fn (Catalog $record): string => $record->fileUrl())
                    ->openUrlInNewTab()
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->label('Látható'),
                TextColumn::make('updated_at')
                    ->label('Módosítva')
                    ->dateTime('Y. m. d.')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
