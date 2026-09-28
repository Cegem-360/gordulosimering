<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Models\UserDiscount;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('name')
                            ->weight('bold')
                            ->columnSpan(2),
                        TextEntry::make('is_admin')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Admin' : 'Vevő')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('email')
                            ->icon('heroicon-m-envelope')
                            ->copyable(),
                        TextEntry::make('phone')
                            ->icon('heroicon-m-phone')
                            ->placeholder('-'),
                        TextEntry::make('email_verified_at')
                            ->dateTime()
                            ->placeholder('Nincs megerősítve'),
                    ]),
                Section::make('Kedvezmények')
                    ->schema([
                        TextEntry::make('discount_summary')
                            ->hiddenLabel()
                            ->badge()
                            ->color('info')
                            ->state(fn (User $record): array => [
                                'Alap: ' . self::formatPercentage($record->base_discount_percentage) . '%',
                                ...$record->discounts()
                                    ->with('discountGroup')
                                    ->get()
                                    ->map(fn (UserDiscount $discount): string => $discount->discountGroup->code . ': ' . self::formatPercentage($discount->percentage) . '%')
                                    ->all(),
                            ]),
                        TextEntry::make('orders_count')
                            ->label('Rendelések száma')
                            ->state(fn (User $record): int => $record->orders()->count()),
                    ]),
                Grid::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Számlázási adatok')
                                    ->schema([
                                        TextEntry::make('billing_name')->placeholder('-'),
                                        TextEntry::make('billing_company_name')->placeholder('-'),
                                        TextEntry::make('billing_vat_number')->placeholder('-'),
                                        TextEntry::make('billing_company_office')->placeholder('-'),
                                        TextEntry::make('billing_address')
                                            ->label('Cím')
                                            ->state(fn (User $record): string => self::formatAddress(
                                                $record->billing_postcode,
                                                $record->billing_city,
                                                $record->billing_address_1,
                                                $record->billing_address_2,
                                                $record->billing_state,
                                                $record->billing_country,
                                            )),
                                    ]),
                            ]),
                        Group::make()
                            ->schema([
                                Section::make('Szállítási cím')
                                    ->schema([
                                        TextEntry::make('shipping_name')->placeholder('-'),
                                        TextEntry::make('shipping_address')
                                            ->label('Cím')
                                            ->state(fn (User $record): string => self::formatAddress(
                                                $record->shipping_postcode,
                                                $record->shipping_city,
                                                $record->shipping_address_1,
                                                $record->shipping_address_2,
                                                $record->shipping_state,
                                                $record->shipping_country,
                                            )),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    private static function formatPercentage(int|float|string|null $value): string
    {
        return mb_rtrim(mb_rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }

    private static function formatAddress(?string ...$parts): string
    {
        $line = implode(' ', array_filter([$parts[0], $parts[1]]));
        $lines = array_filter([$line, $parts[2], $parts[3], $parts[4], $parts[5]]);

        return $lines === [] ? '-' : implode("\n", $lines);
    }
}
