<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

final class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('group_code')
                ->guess(['Csoportkód']),
            ImportColumn::make('product_code')
                ->guess(['Termékkód']),
            ImportColumn::make('is_service')
                ->boolean()
                ->guess(['Szolgáltatás']),
            ImportColumn::make('name')
                ->guess(['Terméknév']),
            ImportColumn::make('slug')
                ->default(fn (array $data): string => Str::slug($data['name'])),
            ImportColumn::make('catalog_number')
                ->guess(['Katalógus szám']),
            ImportColumn::make('type')
                ->guess(['Típus']),
            ImportColumn::make('size')
                ->guess(['Méret']),
            ImportColumn::make('weight')
                ->numeric()
                ->guess(['Súly']),
            ImportColumn::make('rating')
                ->guess(['Minosítés']),
            ImportColumn::make('quality')
                ->guess(['Minoség']),
            ImportColumn::make('product_variety')
                ->guess(['Termékféleség']),
            ImportColumn::make('trade_type')
                ->guess(['Ker. típus']),
            ImportColumn::make('usage_type')
                ->guess(['Felh. típus']),
            ImportColumn::make('currency_settlement')
                ->guess(['Deviza elsz.']),
            ImportColumn::make('discount_group')
                ->guess(['Kedvezmény csoport']),
            ImportColumn::make('is_on_sale')
                ->boolean()
                ->guess(['Akciós ?']),
            ImportColumn::make('sale_percentage')
                ->numeric()
                ->guess(['Akció %']),
            ImportColumn::make('pricing')
                ->guess(['Árképzés']),
            ImportColumn::make('list_price')
                ->numeric()
                ->guess(['Listaár']),
            ImportColumn::make('list_discount')
                ->numeric()
                ->guess(['Lista kedvezmény']),
            ImportColumn::make('purchase_currency_price')
                ->numeric()
                ->guess(['Beszerzési dev. ár']),
            ImportColumn::make('currency')
                ->guess(['Devizanem']),
            ImportColumn::make('currency_multiplier')
                ->numeric()
                ->guess(['Deviza szorzó']),
            ImportColumn::make('purchase_price')
                ->numeric()
                ->guess(['Beszerzési ár']),
            ImportColumn::make('profit_margin')
                ->numeric()
                ->guess(['Haszonkulcs']),
            ImportColumn::make('net_selling_price')
                ->numeric()
                ->guess(['Nettó eladási ár']),
            ImportColumn::make('vat_class')
                ->guess(['ÁFA osztály']),
            ImportColumn::make('gross_selling_price')
                ->numeric()
                ->guess(['Bruttó eladási ár']),
            ImportColumn::make('quantity_unit')
                ->guess(['Mennyiségi egység']),
            ImportColumn::make('secondary_unit')
                ->guess(['Másodlagos me.']),
            ImportColumn::make('minimum_stock')
                ->numeric()
                ->rules(['integer'])
                ->guess(['Minimum készlet']),
            ImportColumn::make('maximum_stock')
                ->numeric()
                ->rules(['integer'])
                ->guess(['Maximum készlet']),
            ImportColumn::make('buffer_stock')
                ->numeric()
                ->rules(['integer'])
                ->guess(['Puffer készlet']),
            ImportColumn::make('order_unit')
                ->numeric()
                ->rules(['integer'])
                ->guess(['Rendelési egység']),
            ImportColumn::make('ksh_prefix')
                ->guess(['KSH elotag']),
            ImportColumn::make('ksh_number')
                ->guess(['KSZ szám']),
            ImportColumn::make('supplier')
                ->guess(['Beszállító']),
            ImportColumn::make('short_note')
                ->guess(['Rövid megjegyzés']),
            ImportColumn::make('description')
                ->guess(['Hosszú megjegyzés']),
            ImportColumn::make('barcode')
                ->guess(['Vonalkód']),
            ImportColumn::make('ean_code')
                ->guess(['EAN kód']),
            ImportColumn::make('min_order_quantity')
                ->numeric()
                ->rules(['integer'])
                ->guess(['Min. rendelheto']),
            ImportColumn::make('trade_quantity')
                ->numeric()
                ->rules(['integer'])
                ->guess(['Ker. mennyiség']),
            ImportColumn::make('pallet_quantity')
                ->numeric()
                ->rules(['integer'])
                ->guess(['Raklap menny.']),
            ImportColumn::make('custom_fields'),
            ImportColumn::make('images'),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }

    public function resolveRecord(): Product
    {
        if (! isset($this->data['slug']) || empty($this->data['slug'])) {
            $this->data['slug'] = Str::slug($this->data['name']);
        }

        return Product::firstOrNew([
            'slug' => $this->data['slug'],
        ]);
    }
}
