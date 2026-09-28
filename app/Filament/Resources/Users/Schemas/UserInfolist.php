<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Models\UserDiscount;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email'),
                TextEntry::make('base_discount_percentage')
                    ->suffix('%'),
                TextEntry::make('discounts')
                    ->state(fn (User $record): array => $record->discounts()
                        ->with('discountGroup')
                        ->get()
                        ->map(fn (UserDiscount $discount): string => $discount->discountGroup->code . ': ' . mb_rtrim(mb_rtrim($discount->percentage, '0'), '.') . '%')
                        ->all())
                    ->badge()
                    ->placeholder('-'),
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
