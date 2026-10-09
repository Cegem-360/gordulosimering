<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuoteRequests\Schemas;

use App\Models\QuoteRequest;
use App\Models\QuoteRequestItem;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Number;

/**
 * The page the shop lands on from the list: who asked, for what, and what
 * it comes to at list prices. Editing is one click away for the status.
 */
final class QuoteRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ajánlatkérő')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('reference')->label('Azonosító')->copyable(),
                        TextEntry::make('status')->label('Állapot')->badge(),
                        TextEntry::make('created_at')->label('Beérkezett')->dateTime('Y. m. d. H:i'),
                        TextEntry::make('name')->label('Név'),
                        TextEntry::make('company')->label('Cégnév')->placeholder('—'),
                        TextEntry::make('user.email')->label('Regisztrált vevő')->placeholder('nem'),
                        TextEntry::make('email')
                            ->label('E-mail')
                            ->copyable()
                            ->url(fn (QuoteRequest $record): string => 'mailto:' . $record->email),
                        TextEntry::make('phone')
                            ->label('Telefon')
                            ->copyable()
                            ->url(fn (QuoteRequest $record): string => 'tel:' . preg_replace('/[^\d+]/', '', $record->phone)),
                    ]),

                Section::make('Üzenet')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('message')
                            ->hiddenLabel()
                            ->placeholder('Nem írt üzenetet.'),
                    ]),

                Section::make('Kért termékek')
                    ->columnSpanFull()
                    ->visible(fn (QuoteRequest $record): bool => $record->items->isNotEmpty())
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->columns(5)
                            ->schema([
                                TextEntry::make('product_name')
                                    ->label('Termék')
                                    ->weight(FontWeight::Bold)
                                    ->url(fn (QuoteRequestItem $record): ?string => $record->product
                                        ? route('products.show', $record->product)
                                        : null)
                                    ->openUrlInNewTab()
                                    ->columnSpan(2),
                                TextEntry::make('product_code')->label('Termékkód')->placeholder('—'),
                                TextEntry::make('quantity')->label('Mennyiség')->suffix(' db'),
                                TextEntry::make('unit_price')
                                    ->label('Listaár (nettó)')
                                    ->placeholder('—')
                                    ->formatStateUsing(fn (?string $state): string => Number::currency((float) $state, 'HUF', 'hu', 0)),
                            ]),
                        TextEntry::make('indicative_net_total')
                            ->label('Listaáron összesen (nettó)')
                            ->state(fn (QuoteRequest $record): string => Number::currency($record->indicativeNetTotal(), 'HUF', 'hu', 0))
                            ->weight(FontWeight::Bold)
                            ->helperText('A beküldéskori listaárakkal (vevőkedvezménnyel); az ajánlat ettől eltérhet.'),
                    ]),

                Section::make('Ügyintézés')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('handled_at')->label('Lezárva')->dateTime('Y. m. d. H:i')->placeholder('—'),
                        TextEntry::make('admin_note')->label('Belső megjegyzés')->placeholder('—'),
                    ]),
            ]);
    }
}
