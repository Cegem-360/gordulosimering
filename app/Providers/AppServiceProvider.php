<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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

        Table::configureUsing(fn (Table $table): Table => $table->reorderableColumns());
        Column::configureUsing(fn (Column $column): Column => $column->toggleable());
    }
}
