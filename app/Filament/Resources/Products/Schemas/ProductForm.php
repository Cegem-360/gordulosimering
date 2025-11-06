<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('group_code'),
                TextInput::make('product_code'),
                Toggle::make('is_service'),
                TextInput::make('name'),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('catalog_number'),
                TextInput::make('type'),
                TextInput::make('size'),
                TextInput::make('weight')
                    ->numeric(),
                TextInput::make('rating'),
                TextInput::make('quality'),
                TextInput::make('product_variety'),
                TextInput::make('trade_type'),
                TextInput::make('usage_type'),
                TextInput::make('currency_settlement'),
                TextInput::make('discount_group'),
                Toggle::make('is_on_sale'),
                TextInput::make('sale_percentage')
                    ->numeric(),
                TextInput::make('pricing'),
                TextInput::make('list_price')
                    ->numeric(),
                TextInput::make('list_discount')
                    ->numeric(),
                TextInput::make('purchase_currency_price')
                    ->numeric(),
                TextInput::make('currency'),
                TextInput::make('currency_multiplier')
                    ->numeric(),
                TextInput::make('purchase_price')
                    ->numeric(),
                TextInput::make('profit_margin')
                    ->numeric(),
                TextInput::make('net_selling_price')
                    ->numeric(),
                TextInput::make('vat_class'),
                TextInput::make('gross_selling_price')
                    ->numeric(),
                TextInput::make('quantity_unit'),
                TextInput::make('secondary_unit'),
                TextInput::make('minimum_stock')
                    ->numeric(),
                TextInput::make('maximum_stock')
                    ->numeric(),
                TextInput::make('buffer_stock')
                    ->numeric(),
                TextInput::make('order_unit')
                    ->numeric(),
                TextInput::make('ksh_prefix'),
                TextInput::make('ksh_number'),
                TextInput::make('supplier'),
                TextInput::make('short_note'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('barcode'),
                TextInput::make('ean_code'),
                TextInput::make('min_order_quantity')
                    ->numeric(),
                TextInput::make('trade_quantity')
                    ->numeric(),
                TextInput::make('pallet_quantity')
                    ->numeric(),
                Textarea::make('custom_fields')
                    ->columnSpanFull(),
                Textarea::make('images')
                    ->columnSpanFull(),
            ]);
    }
}
