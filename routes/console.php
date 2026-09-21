<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:import-product-images')
    ->weeklyOn(1, '03:00')
    ->withoutOverlapping()
    ->runInBackground();

/**
 * A típusképek a termékkód szerinti képek után futnak: csak azokat a termékeket
 * töltik fel, amelyeknek az előző import nem adott egyedi képet.
 */
Schedule::command('app:import-type-images')
    ->weeklyOn(1, '03:30')
    ->withoutOverlapping()
    ->runInBackground();
