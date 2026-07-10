<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('billing_name')
                    ->placeholder('-'),
                TextEntry::make('billing_company_name')
                    ->placeholder('-'),
                TextEntry::make('billing_vat_number')
                    ->placeholder('-'),
                TextEntry::make('billing_company_office')
                    ->placeholder('-'),
                TextEntry::make('billing_postcode')
                    ->placeholder('-'),
                TextEntry::make('billing_city')
                    ->placeholder('-'),
                TextEntry::make('billing_address_1')
                    ->placeholder('-'),
                TextEntry::make('billing_address_2')
                    ->placeholder('-'),
                TextEntry::make('billing_country')
                    ->placeholder('-'),
                TextEntry::make('billing_state')
                    ->placeholder('-'),
                TextEntry::make('shipping_name')
                    ->placeholder('-'),
                TextEntry::make('shipping_postcode')
                    ->placeholder('-'),
                TextEntry::make('shipping_city')
                    ->placeholder('-'),
                TextEntry::make('shipping_address_1')
                    ->placeholder('-'),
                TextEntry::make('shipping_address_2')
                    ->placeholder('-'),
                TextEntry::make('shipping_country')
                    ->placeholder('-'),
                TextEntry::make('shipping_state')
                    ->placeholder('-'),
            ]);
    }
}
