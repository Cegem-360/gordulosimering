<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Filament\Imports\Concerns\CastsHungarianValues;
use App\Models\Product;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Override;

final class ProductImporter extends Importer
{
    use CastsHungarianValues;

    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('group_code')
                ->guess(['Csoportkód', ...self::exportHeaderGuess('group_code')]),
            ImportColumn::make('product_code')
                ->guess(['Termékkód', ...self::exportHeaderGuess('product_code')]),
            self::booleanColumn('is_service')
                ->guess(['Szolgáltatás', ...self::exportHeaderGuess('is_service')]),
            self::booleanColumn('is_web_visible')
                ->guess(['WEBÁRUHÁZBAN SZEREPELJEN', ...self::exportHeaderGuess('is_web_visible')]),
            self::booleanColumn('is_inactive')
                ->guess(['Inaktív', ...self::exportHeaderGuess('is_inactive')]),
            ImportColumn::make('name')
                ->requiredMappingForNewRecordsOnly()
                ->rules(['required', 'max:255'])
                ->guess(['Terméknév', ...self::exportHeaderGuess('name')]),
            ImportColumn::make('slug')
                ->rules(['nullable', 'max:255'])
                ->guess(self::exportHeaderGuess('slug')),
            ImportColumn::make('catalog_number')
                ->guess(['Katalógus szám', ...self::exportHeaderGuess('catalog_number')]),
            ImportColumn::make('type')
                ->guess(['Típus', ...self::exportHeaderGuess('type')]),
            ImportColumn::make('size')
                ->guess(['Méret', ...self::exportHeaderGuess('size')]),
            self::numericColumn('weight')
                ->guess(['Súly', ...self::exportHeaderGuess('weight')]),
            ImportColumn::make('rating')
                ->guess(['Minosítés', ...self::exportHeaderGuess('rating')]),
            ImportColumn::make('quality')
                ->guess(['Minoség', ...self::exportHeaderGuess('quality')]),
            ImportColumn::make('product_variety')
                ->guess(['Termékféleség', ...self::exportHeaderGuess('product_variety')]),
            ImportColumn::make('trade_type')
                ->guess(['Ker. típus', ...self::exportHeaderGuess('trade_type')]),
            ImportColumn::make('usage_type')
                ->guess(['Felh. típus', ...self::exportHeaderGuess('usage_type')]),
            ImportColumn::make('currency_settlement')
                ->guess(['Deviza elsz.', ...self::exportHeaderGuess('currency_settlement')]),
            ImportColumn::make('discount_group')
                ->guess(['Kedvezmény csoport', ...self::exportHeaderGuess('discount_group')]),
            self::booleanColumn('is_on_sale')
                ->guess(['Akciós ?', ...self::exportHeaderGuess('is_on_sale')]),
            self::numericColumn('sale_percentage')
                ->guess(['Akció %', ...self::exportHeaderGuess('sale_percentage')]),
            ImportColumn::make('pricing')
                ->guess(['Árképzés', ...self::exportHeaderGuess('pricing')]),
            self::numericColumn('net_selling_price')
                ->guess(['Nettó eladási ár', ...self::exportHeaderGuess('net_selling_price')]),
            ImportColumn::make('vat_class')
                ->guess(['ÁFA osztály', ...self::exportHeaderGuess('vat_class')]),
            self::numericColumn('gross_selling_price')
                ->guess(['Bruttó eladási ár', ...self::exportHeaderGuess('gross_selling_price')]),
            ImportColumn::make('quantity_unit')
                ->guess(['Mennyiségi egység', ...self::exportHeaderGuess('quantity_unit')]),
            ImportColumn::make('secondary_unit')
                ->guess(['Másodlagos me.', ...self::exportHeaderGuess('secondary_unit')]),
            self::numericColumn('minimum_stock', integer: true)
                ->guess(['Minimum készlet', ...self::exportHeaderGuess('minimum_stock')]),
            self::numericColumn('maximum_stock', integer: true)
                ->guess(['Maximum készlet', ...self::exportHeaderGuess('maximum_stock')]),
            self::numericColumn('buffer_stock', integer: true)
                ->guess(['Puffer készlet', ...self::exportHeaderGuess('buffer_stock')]),
            self::numericColumn('order_unit', integer: true)
                ->guess(['Rendelési egység', ...self::exportHeaderGuess('order_unit')]),
            ImportColumn::make('ksh_prefix')
                ->guess(['KSH elotag', ...self::exportHeaderGuess('ksh_prefix')]),
            ImportColumn::make('ksh_number')
                ->guess(['KSZ szám', ...self::exportHeaderGuess('ksh_number')]),
            ImportColumn::make('supplier')
                ->guess(['Beszállító', ...self::exportHeaderGuess('supplier')]),
            ImportColumn::make('short_note')
                ->guess(['Rövid megjegyzés', ...self::exportHeaderGuess('short_note')]),
            ImportColumn::make('description')
                ->guess(['Hosszú megjegyzés', ...self::exportHeaderGuess('description')]),
            ImportColumn::make('barcode')
                ->guess(['Vonalkód', ...self::exportHeaderGuess('barcode')]),
            ImportColumn::make('ean_code')
                ->guess(['EAN kód', ...self::exportHeaderGuess('ean_code')]),
            self::numericColumn('min_order_quantity', integer: true)
                ->guess(['Min. rendelheto', ...self::exportHeaderGuess('min_order_quantity')]),
            self::numericColumn('trade_quantity', integer: true)
                ->guess(['Ker. mennyiség', ...self::exportHeaderGuess('trade_quantity')]),
            self::numericColumn('pallet_quantity', integer: true)
                ->guess(['Raklap menny.', ...self::exportHeaderGuess('pallet_quantity')]),
            ImportColumn::make('custom_fields')
                ->castStateUsing(self::decodeJson(...))
                ->rules(['nullable', 'array'])
                ->guess(self::exportHeaderGuess('custom_fields')),
            ImportColumn::make('images')
                ->castStateUsing(self::decodeJson(...))
                ->rules(['nullable', 'array'])
                ->guess(self::exportHeaderGuess('images')),
            ImportColumn::make('categories')
                ->relationship(resolveUsing: 'name')
                ->multiple(',')
                ->guess(self::exportHeaderGuess('categories')),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'A termékimport befejeződött, ' . Number::format($import->successful_rows) . ' sor importálva.';

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
            $body .= ' ' . Number::format($failedRowsCount) . ' sor importálása nem sikerült.';
        }

        return $body;
    }

    /**
     * A terméket a termékkód, annak hiányában az URL-kulcs azonosítja;
     * ha egyik sem talál, új termék jön létre.
     */
    #[Override]
    public function resolveRecord(): Product
    {
        $productCode = mb_trim((string) ($this->data['product_code'] ?? ''));
        $slug = mb_trim((string) ($this->data['slug'] ?? ''));

        return ($productCode === '' ? null : Product::query()->where('product_code', $productCode)->first())
            ?? ($slug === '' ? null : Product::query()->where('slug', $slug)->first())
            ?? new Product();
    }

    protected function beforeSave(): void
    {
        if (blank($this->record->slug)) {
            $this->record->slug = $this->uniqueSlug(Str::slug((string) $this->record->product_code) ?: Str::slug((string) $this->record->name) ?: 'termek');

            return;
        }

        if (Product::query()->where('slug', $this->record->slug)->whereKeyNot($this->record->getKey())->exists()) {
            throw new RowImportFailedException('A(z) „' . $this->record->slug . '” URL-kulcs már egy másik termékhez tartozik.');
        }
    }

    /**
     * Az export JSON-ként írja a tömb mezőket ("["a.jpg"]").
     *
     * @return array<mixed>|string|null
     */
    private static function decodeJson(?string $state): array|string|null
    {
        if (blank($state)) {
            return null;
        }

        $decoded = json_decode($state, true);

        return is_array($decoded) ? $decoded : $state;
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $suffix = 0;

        while (Product::query()->where('slug', $slug)->whereKeyNot($this->record->getKey())->exists()) {
            $suffix++;
            $slug = $base . '-' . $suffix;
        }

        return $slug;
    }
}
