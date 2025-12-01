<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Order ID')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('shippingMethod.name')
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->searchable(),
                TextColumn::make('payment_method_title')
                    ->searchable(),
                IconColumn::make('set_paid')
                    ->boolean(),
                TextColumn::make('billing_name')
                    ->searchable(),
                TextColumn::make('billing_address_1')
                    ->searchable(),
                TextColumn::make('billing_address_2')
                    ->searchable(),
                TextColumn::make('billing_city')
                    ->searchable(),
                TextColumn::make('billing_state')
                    ->searchable(),
                TextColumn::make('billing_postcode')
                    ->searchable(),
                TextColumn::make('billing_country')
                    ->searchable(),
                TextColumn::make('billing_email')
                    ->searchable(),
                TextColumn::make('billing_phone')
                    ->searchable(),
                TextColumn::make('billing_vat_number')
                    ->searchable(),
                TextColumn::make('billing_company_name')
                    ->searchable(),
                TextColumn::make('billing_company_office')
                    ->searchable(),
                TextColumn::make('shipping_name')
                    ->searchable(),
                TextColumn::make('shipping_address_1')
                    ->searchable(),
                TextColumn::make('shipping_address_2')
                    ->searchable(),
                TextColumn::make('shipping_city')
                    ->searchable(),
                TextColumn::make('shipping_state')
                    ->searchable(),
                TextColumn::make('shipping_postcode')
                    ->searchable(),
                TextColumn::make('shipping_country')
                    ->searchable(),
                TextColumn::make('shipping_tracking_number')
                    ->searchable(),
                TextColumn::make('order_key')
                    ->searchable(),
                TextColumn::make('order_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('order_currency')
                    ->searchable(),
                TextColumn::make('shipping_cost')
                    ->money()
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
