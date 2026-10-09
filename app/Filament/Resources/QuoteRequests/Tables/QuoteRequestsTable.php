<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuoteRequests\Tables;

use App\Enums\QuoteRequestStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class QuoteRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('Azonosító')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('Név')
                    ->description(fn ($record): ?string => $record->company)
                    ->searchable(['name', 'company']),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label('Tételek')
                    ->counts('items')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Állapot')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Beérkezett')
                    ->dateTime('Y. m. d. H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Állapot')
                    ->options(QuoteRequestStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
