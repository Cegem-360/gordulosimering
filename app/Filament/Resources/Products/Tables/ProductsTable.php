<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Imports\ProductImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group_code')
                    ->searchable(),
                TextColumn::make('product_code')
                    ->searchable(),
                IconColumn::make('is_service')
                    ->boolean(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('catalog_number')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('size')
                    ->searchable(),
                TextColumn::make('weight')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rating')
                    ->searchable(),
                TextColumn::make('quality')
                    ->searchable(),
                TextColumn::make('product_variety')
                    ->searchable(),
                TextColumn::make('trade_type')
                    ->searchable(),
                TextColumn::make('usage_type')
                    ->searchable(),
                TextColumn::make('currency_settlement')
                    ->searchable(),
                TextColumn::make('discount_group')
                    ->searchable(),
                IconColumn::make('is_on_sale')
                    ->boolean(),
                TextColumn::make('sale_percentage')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pricing')
                    ->searchable(),
                TextColumn::make('list_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('list_discount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('purchase_currency_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable(),
                TextColumn::make('currency_multiplier')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('purchase_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('profit_margin')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('net_selling_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vat_class')
                    ->searchable(),
                TextColumn::make('gross_selling_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantity_unit')
                    ->searchable(),
                TextColumn::make('secondary_unit')
                    ->searchable(),
                TextColumn::make('minimum_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('maximum_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('buffer_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('order_unit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ksh_prefix')
                    ->searchable(),
                TextColumn::make('ksh_number')
                    ->searchable(),
                TextColumn::make('supplier')
                    ->searchable(),
                TextColumn::make('short_note')
                    ->searchable(),
                TextColumn::make('barcode')
                    ->searchable(),
                TextColumn::make('ean_code')
                    ->searchable(),
                TextColumn::make('min_order_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('trade_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pallet_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ImportAction::make('products_import')
                    ->label('Import Products')
                    ->importer(ProductImporter::class),
            ]);
    }
}
