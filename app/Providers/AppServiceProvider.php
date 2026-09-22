<?php

declare(strict_types=1);

namespace App\Providers;

use App\Filament\Support\FieldLabel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Override;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('global', fn (Request $request) => Limit::perMinute(50));

        Table::configureUsing(fn (Table $table): Table => $table->reorderableColumns()->defaultCurrency('HUF'));
        Schema::configureUsing(fn (Schema $schema): Schema => $schema->defaultCurrency('HUF'));
        Column::configureUsing(fn (Column $column): Column => $column->toggleable());

        FieldLabel::registerAsDefaultLabel();

        Resource::titleCaseModelLabel(false);

        Number::useLocale('hu');
        Number::useCurrency('HUF');
    }
}
