<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationRules\Tables;

use App\Filament\Resources\RecommendationRules\RecommendationRuleResource;
use App\Models\RecommendationRule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

final class RecommendationRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->columns([
                TextColumn::make('source')
                    ->label('Ezt vásárolják')
                    ->state(fn (RecommendationRule $record): string => $record->source_category_id === null
                        ? 'Minden termék'
                        : (RecommendationRuleResource::categoryPaths()[$record->source_category_id] ?? '–')),
                TextColumn::make('target')
                    ->label('Ezt kínáljuk mellé')
                    ->state(fn (RecommendationRule $record): string => $record->target_category_id === null
                        ? 'Cikkszám: ' . $record->target_code_prefix . '…'
                        : (RecommendationRuleResource::categoryPaths()[$record->target_category_id] ?? '–')),
                ToggleColumn::make('is_active')
                    ->label('Aktív'),
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
