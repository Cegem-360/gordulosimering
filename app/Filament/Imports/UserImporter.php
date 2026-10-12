<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\CustomerType;
use App\Filament\Imports\Concerns\CastsHungarianValues;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Override;

/**
 * Vevők importja. A jelszó és az admin jog szándékosan nem importálható:
 * az új felhasználó véletlen jelszót kap, és az "Elfelejtett jelszó"
 * funkcióval állíthat be sajátot.
 */
final class UserImporter extends Importer
{
    use CastsHungarianValues;

    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMappingForNewRecordsOnly()
                ->rules(['required', 'max:255']),
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),
            ImportColumn::make('phone'),
            ImportColumn::make('customer_type')
                ->castStateUsing(fn (string $state): string => collect(CustomerType::cases())
                    ->first(fn (CustomerType $type): bool => in_array(mb_strtolower($state), [$type->value, mb_strtolower($type->getLabel())], true))
                    ?->value ?? $state)
                ->rules(['nullable', Rule::enum(CustomerType::class)]),
            self::numericColumn('base_discount_percentage', nullable: false)
                ->rules(['nullable', 'numeric', 'min:0', 'max:100']),
            ImportColumn::make('billing_name'),
            ImportColumn::make('billing_company_name'),
            ImportColumn::make('billing_vat_number'),
            ImportColumn::make('billing_company_office'),
            ImportColumn::make('billing_postcode'),
            ImportColumn::make('billing_city'),
            ImportColumn::make('billing_address_1'),
            ImportColumn::make('billing_address_2'),
            ImportColumn::make('billing_country'),
            ImportColumn::make('billing_state'),
            ImportColumn::make('shipping_name'),
            ImportColumn::make('shipping_postcode'),
            ImportColumn::make('shipping_city'),
            ImportColumn::make('shipping_address_1'),
            ImportColumn::make('shipping_address_2'),
            ImportColumn::make('shipping_country'),
            ImportColumn::make('shipping_state'),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'A felhasználóimport befejeződött, ' . Number::format($import->successful_rows) . ' sor importálva.';

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
            $body .= ' ' . Number::format($failedRowsCount) . ' sor importálása nem sikerült.';
        }

        return $body;
    }

    /**
     * A felhasználót az e-mail-cím azonosítja; ismeretlen címmel új jön létre.
     */
    #[Override]
    public function resolveRecord(): User
    {
        return User::query()->firstOrNew(['email' => mb_trim((string) $this->data['email'])]);
    }

    protected function beforeSave(): void
    {
        if (! $this->record->exists) {
            $this->record->password = Str::password(32);
        }
    }
}
