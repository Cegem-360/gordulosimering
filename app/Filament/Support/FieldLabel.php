<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Filament\Actions\Imports\ImportColumn;
use Filament\Forms\Components\Field;
use Filament\Infolists\Components\Entry;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Support\Facades\Lang;

final class FieldLabel
{
    /**
     * Filament component classes whose default label is resolved from the
     * `fields` translations. Each is a base class, so every column, field,
     * entry, filter and import column inherits the behaviour.
     *
     * @var list<class-string>
     */
    private const array COMPONENTS = [
        Column::class,
        Field::class,
        Entry::class,
        BaseFilter::class,
        ImportColumn::class,
    ];

    public static function registerAsDefaultLabel(): void
    {
        foreach (self::COMPONENTS as $component) {
            $component::configureUsing(self::apply(...));
        }
    }

    public static function apply(Column|Field|Entry|BaseFilter|ImportColumn $component): void
    {
        $label = self::for($component->getName());

        if ($label !== null) {
            $component->label($label);
        }
    }

    public static function for(string $attribute): ?string
    {
        $key = 'fields.' . $attribute;

        if (! Lang::has($key)) {
            return null;
        }

        $label = Lang::get($key);

        return is_string($label) ? $label : null;
    }
}
