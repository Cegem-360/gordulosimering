<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Filament\Imports\Concerns\CastsHungarianValues;
use App\Models\Category;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Override;

final class CategoryImporter extends Importer
{
    use CastsHungarianValues;

    protected static ?string $model = Category::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMappingForNewRecordsOnly()
                ->rules(['required', 'max:255']),
            ImportColumn::make('slug')
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('parentCategory')
                ->label('Szülő kategória')
                ->relationship(resolveUsing: ['slug', 'name'])
                ->guess(['category_id', 'Szülő kategória']),
            ImportColumn::make('description'),
            ImportColumn::make('display'),
            self::numericColumn('sort_order', integer: true),
            ImportColumn::make('image'),
            self::booleanColumn('is_featured', nullable: false),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'A kategóriaimport befejeződött, ' . Number::format($import->successful_rows) . ' sor importálva.';

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
            $body .= ' ' . Number::format($failedRowsCount) . ' sor importálása nem sikerült.';
        }

        return $body;
    }

    /**
     * A kategóriát az URL-kulcs, annak hiányában a pontos név azonosítja;
     * ha egyik sem talál, új kategória jön létre.
     */
    #[Override]
    public function resolveRecord(): Category
    {
        $slug = mb_trim((string) ($this->data['slug'] ?? ''));
        $name = mb_trim((string) ($this->data['name'] ?? ''));

        return ($slug === '' ? null : Category::query()->where('slug', $slug)->first())
            ?? ($name === '' ? null : Category::query()->where('name', $name)->first())
            ?? new Category();
    }

    protected function beforeSave(): void
    {
        if ($this->record->exists && $this->record->category_id === $this->record->getKey()) {
            throw new RowImportFailedException('A kategória nem lehet a saját szülője.');
        }

        if (blank($this->record->slug)) {
            $this->record->slug = $this->uniqueSlug(Str::slug((string) $this->record->name) ?: 'kategoria');

            return;
        }

        if (Category::query()->where('slug', $this->record->slug)->whereKeyNot($this->record->getKey())->exists()) {
            throw new RowImportFailedException('A(z) „' . $this->record->slug . '” URL-kulcs már egy másik kategóriához tartozik.');
        }
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $suffix = 0;

        while (Category::query()->where('slug', $slug)->whereKeyNot($this->record->getKey())->exists()) {
            $suffix++;
            $slug = $base . '-' . $suffix;
        }

        return $slug;
    }
}
