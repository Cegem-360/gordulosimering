<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name'),
                Select::make('shipping_method_id')
                    ->relationship('shippingMethod', 'name')
                    ->required(),
                TextInput::make('payment_method')
                    ->required()
                    ->default('bacs'),
                TextInput::make('payment_method_title')
                    ->required()
                    ->default('Bank Transfer'),
                Toggle::make('set_paid')
                    ->required(),
                TextInput::make('billing_name'),
                TextInput::make('billing_address_1'),
                TextInput::make('billing_address_2'),
                TextInput::make('billing_city'),
                TextInput::make('billing_state'),
                TextInput::make('billing_postcode'),
                TextInput::make('billing_country'),
                TextInput::make('billing_email')
                    ->email(),
                TextInput::make('billing_phone')
                    ->tel(),
                TextInput::make('billing_vat_number'),
                TextInput::make('billing_company_name'),
                TextInput::make('billing_company_office'),
                TextInput::make('shipping_name'),
                TextInput::make('shipping_address_1'),
                TextInput::make('shipping_address_2'),
                TextInput::make('shipping_city'),
                TextInput::make('shipping_state'),
                TextInput::make('shipping_postcode'),
                TextInput::make('shipping_country'),
                TextInput::make('shipping_tracking_number')
                    ->required()
                    ->default('null'),
                TextInput::make('order_key'),
                Select::make('order_status')
                    ->options(OrderStatus::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('order_currency')
                    ->required()
                    ->default('HUF'),
                TextInput::make('shipping_cost')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
            ]);
    }
}
