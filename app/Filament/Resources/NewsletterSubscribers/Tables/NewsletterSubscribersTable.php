<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterSubscribers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class NewsletterSubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')
                    ->label('E-mail cím')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('created_at')
                    ->label('Feliratkozás')
                    ->dateTime('Y. m. d. H:i')
                    ->sortable(),
            ])
            ->recordActions([
                DeleteAction::make()->label('Leiratkoztatás'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
