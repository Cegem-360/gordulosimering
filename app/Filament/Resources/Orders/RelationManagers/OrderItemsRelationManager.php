<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $title = 'Rendelés tételei';

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
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $product = Product::find($state);
                            if ($product) {
                                $set('subtotal', $product->net_selling_price);
                                $set('total', $product->net_selling_price);
                            }
                        }
                    }),
                TextInput::make('quantity')
                    ->label('Mennyiség')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required(),
                TextInput::make('subtotal')
                    ->label('Nettó egységár')
                    ->numeric()
                    ->prefix('Ft')
                    ->required(),
                TextInput::make('subtotal_tax')
                    ->label('ÁFA egységár')
                    ->numeric()
                    ->prefix('Ft')
                    ->default(0),
                TextInput::make('total')
                    ->label('Nettó összesen')
                    ->numeric()
                    ->prefix('Ft')
                    ->required(),
                TextInput::make('total_tax')
                    ->label('ÁFA összesen')
                    ->numeric()
                    ->prefix('Ft')
                    ->default(0),
                TextInput::make('tax_class')
                    ->label('Adóosztály')
                    ->default('27%'),
            ]);
    }

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
                TextColumn::make('subtotal')
                    ->label('Nettó egységár')
                    ->money('HUF')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Nettó összesen')
                    ->money('HUF')
                    ->sortable(),
                TextColumn::make('total_tax')
                    ->label('ÁFA')
                    ->money('HUF')
                    ->sortable(),
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
