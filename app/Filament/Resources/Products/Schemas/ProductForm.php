<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Alapadatok')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Terméknév')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('product_code')
                            ->label('Termékkód'),
                        TextInput::make('group_code')
                            ->label('Csoportkód'),
                        TextInput::make('slug')
                            ->required(),
                        TextInput::make('catalog_number')
                            ->label('Katalógusszám'),
                        TextInput::make('type')
                            ->label('Típus'),
                        TextInput::make('size')
                            ->label('Méret'),
                    ]),

                Section::make('Besorolás és webshop')
                    ->columns(2)
                    ->schema([
                        Select::make('categories')
                            ->label('Kategóriák')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->columnSpanFull(),
                        Toggle::make('is_web_visible')
                            ->label('Webshopban látszik'),
                        Toggle::make('is_inactive')
                            ->label('Inaktív'),
                        Toggle::make('is_service')
                            ->label('Szolgáltatás'),
                        Toggle::make('is_on_sale')
                            ->label('Akciós'),
                        Toggle::make('is_featured')
                            ->label('Kiemelt termék')
                            ->helperText('A főoldal "Kiemelt termékeink" szekciójában jelenik meg.'),
                    ]),

                Section::make('Média')
                    ->schema([
                        FileUpload::make('featured_image')
                            ->label('Kiemelt kép')
                            ->image()
                            ->disk('public')
                            ->directory('products/featured')
                            ->imageEditor()
                            ->columnSpanFull(),
                        FileUpload::make('images')
                            ->label('További képek')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->disk('public')
                            ->directory('products/images')
                            ->columnSpanFull(),
                        FileUpload::make('documents')
                            ->label('Dokumentumok')
                            ->multiple()
                            ->disk('public')
                            ->directory('products/documents')
                            ->storeFileNamesIn('document_names')
                            ->downloadable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Árazás')
                    ->columns(3)
                    ->schema([
                        TextInput::make('pricing')
                            ->label('Árképzés'),
                        TextInput::make('net_selling_price')
                            ->label('Nettó eladási ár')
                            ->numeric()
                            ->suffix('Ft'),
                        TextInput::make('gross_selling_price')
                            ->label('Bruttó eladási ár')
                            ->numeric()
                            ->suffix('Ft'),
                        TextInput::make('vat_class')
                            ->label('ÁFA osztály'),
                        TextInput::make('sale_percentage')
                            ->label('Akció %')
                            ->numeric()
                            ->suffix('%'),
                        TextInput::make('discount_group')
                            ->label('Kedvezmény csoport'),
                    ]),

                Section::make('Készlet és mennyiség')
                    ->columns(3)
                    ->schema([
                        TextInput::make('quantity_unit')
                            ->label('Mennyiségi egység'),
                        TextInput::make('secondary_unit')
                            ->label('Másodlagos egység'),
                        TextInput::make('weight')
                            ->label('Súly')
                            ->numeric(),
                        TextInput::make('minimum_stock')
                            ->label('Minimum készlet')
                            ->numeric(),
                        TextInput::make('maximum_stock')
                            ->label('Maximum készlet')
                            ->numeric(),
                        TextInput::make('buffer_stock')
                            ->label('Puffer készlet')
                            ->numeric(),
                        TextInput::make('order_unit')
                            ->label('Rendelési egység')
                            ->numeric(),
                        TextInput::make('min_order_quantity')
                            ->label('Min. rendelhető')
                            ->numeric(),
                        TextInput::make('trade_quantity')
                            ->label('Ker. mennyiség')
                            ->numeric(),
                        TextInput::make('pallet_quantity')
                            ->label('Raklap mennyiség')
                            ->numeric(),
                    ]),

                Section::make('Egyéb adatok és kódok')
                    ->description('A kapcsolóval a mező termékoldali megjelenítése kapcsolható ki ennél a terméknél.')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        self::withVisibilityToggle(TextInput::make('rating')
                            ->label('Minősítés')),
                        self::withVisibilityToggle(TextInput::make('quality')
                            ->label('Minőség')),
                        self::withVisibilityToggle(TextInput::make('product_variety')
                            ->label('Termékféleség')),
                        TextInput::make('trade_type')
                            ->label('Ker. típus'),
                        TextInput::make('usage_type')
                            ->label('Felh. típus'),
                        TextInput::make('currency_settlement')
                            ->label('Deviza elsz.'),
                        self::withVisibilityToggle(TextInput::make('supplier')
                            ->label('Beszállító')),
                        self::withVisibilityToggle(TextInput::make('barcode')
                            ->label('Vonalkód')),
                        self::withVisibilityToggle(TextInput::make('ean_code')
                            ->label('EAN kód')),
                        self::withVisibilityToggle(TextInput::make('ksh_prefix')
                            ->label('KSH előtag')),
                        self::withVisibilityToggle(TextInput::make('ksh_number')
                            ->label('KSZ szám')),
                        self::withVisibilityToggle(TextInput::make('short_note')
                            ->label('Rövid megjegyzés')
                            ->columnSpanFull()),
                        self::withVisibilityToggle(Textarea::make('description')
                            ->label('Hosszú megjegyzés')
                            ->columnSpanFull()),
                        self::withVisibilityToggle(KeyValue::make('custom_fields')
                            ->label('Egyéni mezők')
                            ->columnSpanFull()),
                    ]),
            ]);
    }

    /**
     * Puts a "Látható" switch beside the field. It is stored per product in
     * `field_visibility` and is on unless switched off, so existing
     * products keep showing everything.
     */
    private static function withVisibilityToggle(Field $field): Flex
    {
        return Flex::make([
            $field,
            Toggle::make('field_visibility.' . $field->getName())
                ->label('Látható')
                ->inline(false)
                ->onIcon(Heroicon::Eye)
                ->offIcon(Heroicon::EyeSlash)
                ->default(true)
                ->afterStateHydrated(fn (Toggle $component, ?Product $record) => $component->state(
                    $record?->showsField($field->getName()) ?? true,
                ))
                ->grow(false),
        ])->columnSpan($field->getColumnSpan());
    }
}
