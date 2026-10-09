<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\OrderItem;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use Override;

final class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $title = 'Rendelés tételei';

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('product_id')
                    ->label('Termék')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (mixed $state, Set $set): void {
                        $product = filled($state) ? Product::query()->find($state) : null;

                        if ($product !== null) {
                            $set('total', $product->net_selling_price);
                        }
                    }),
                TextInput::make('quantity')
                    ->label('Mennyiség')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required(),
                TextInput::make('total')
                    ->label('Nettó egységár')
                    ->numeric()
                    ->prefix('Ft')
                    ->required(),
                TextInput::make('subtotal')
                    ->label('Nettó összesen')
                    ->prefix('Ft')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Mentéskor számolódik: egységár × mennyiség.'),
                TextInput::make('total_tax')
                    ->label('ÁFA egységár')
                    ->prefix('Ft')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('subtotal_tax')
                    ->label('ÁFA összesen')
                    ->prefix('Ft')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Mentéskor számolódik, 27%.'),
            ]);
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Termék')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.product_code')
                    ->label('Cikkszám')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Mennyiség')
                    ->sortable(),
                TextColumn::make('regular_price')
                    ->label('Nettó listaár')
                    ->money('HUF', locale: 'hu')
                    ->placeholder('-'),
                TextColumn::make('discount_percentage')
                    ->label('Kedvezmény')
                    ->formatStateUsing(fn (OrderItem $record): ?string => $record->hasDiscount()
                        ? '−' . Number::percentage((float) $record->discount_percentage, maxPrecision: 2, locale: 'hu')
                        : null)
                    ->badge()
                    ->color('success')
                    ->placeholder('-'),
                TextColumn::make('total')
                    ->label('Nettó egységár')
                    ->money('HUF', locale: 'hu')
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->label('Nettó összesen')
                    ->money('HUF', locale: 'hu')
                    ->sortable(),
                TextColumn::make('subtotal_tax')
                    ->label('ÁFA (27%)')
                    ->money('HUF', locale: 'hu'),
                TextColumn::make('gross_line_total')
                    ->label('Bruttó összesen')
                    ->state(fn (OrderItem $record): float => $record->grossLineTotal())
                    ->money('HUF', locale: 'hu'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
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
