<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\Services\ProductCsvExporter;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('Kép')
                    ->disk('public')
                    ->square(),
                TextColumn::make('group_code')
                    ->searchable(),
                TextColumn::make('product_code')
                    ->searchable(),
                IconColumn::make('is_service')
                    ->boolean(),
                IconColumn::make('is_web_visible')
                    ->boolean(),
                IconColumn::make('is_inactive')
                    ->boolean(),
                ToggleColumn::make('is_featured')
                    ->label('Kiemelt'),
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
                TextColumn::make('net_selling_price')
                    ->money()
                    ->sortable(),
                TextColumn::make('vat_class')
                    ->searchable(),
                TextColumn::make('gross_selling_price')
                    ->money()
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
                TernaryFilter::make('is_featured')
                    ->label('Kiemelt'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('export')
                        ->label('Kijelöltek exportálása (CSV)')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records, ProductCsvExporter $exporter): StreamedResponse => $exporter->download(
                            Product::query()->whereKey($records->modelKeys()),
                            'termekek-kijeloltek-' . now()->format('Y-m-d') . '.csv',
                        )),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
