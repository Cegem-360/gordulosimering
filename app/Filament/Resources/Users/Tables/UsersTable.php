<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Enums\CustomerType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_admin')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('base_discount_percentage')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('discounts_count')
                    ->counts('discounts')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('customer_type')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('billing_name')
                    ->searchable(),
                TextColumn::make('billing_company_name')
                    ->searchable(),
                TextColumn::make('billing_vat_number')
                    ->searchable(),
                TextColumn::make('billing_company_office')
                    ->searchable(),
                TextColumn::make('billing_postcode')
                    ->searchable(),
                TextColumn::make('billing_city')
                    ->searchable(),
                TextColumn::make('billing_address_1')
                    ->searchable(),
                TextColumn::make('billing_address_2')
                    ->searchable(),
                TextColumn::make('billing_country')
                    ->searchable(),
                TextColumn::make('billing_state')
                    ->searchable(),
                TextColumn::make('shipping_name')
                    ->searchable(),
                TextColumn::make('shipping_postcode')
                    ->searchable(),
                TextColumn::make('shipping_city')
                    ->searchable(),
                TextColumn::make('shipping_address_1')
                    ->searchable(),
                TextColumn::make('shipping_address_2')
                    ->searchable(),
                TextColumn::make('shipping_country')
                    ->searchable(),
                TextColumn::make('shipping_state')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('customer_type')
                    ->options(CustomerType::class),
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
