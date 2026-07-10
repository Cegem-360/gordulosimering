<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('billing_name'),
                TextInput::make('billing_company_name'),
                TextInput::make('billing_vat_number'),
                TextInput::make('billing_company_office'),
                TextInput::make('billing_postcode'),
                TextInput::make('billing_city'),
                TextInput::make('billing_address_1'),
                TextInput::make('billing_address_2'),
                TextInput::make('billing_country'),
                TextInput::make('billing_state'),
                TextInput::make('shipping_name'),
                TextInput::make('shipping_postcode'),
                TextInput::make('shipping_city'),
                TextInput::make('shipping_address_1'),
                TextInput::make('shipping_address_2'),
                TextInput::make('shipping_country'),
                TextInput::make('shipping_state'),
            ]);
    }
}
