<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Az ügyfél publikált táblázatából, tiszta lappal: a táblázatból kikerült
 * kódok képe is lekerül a termékekről.
 */
Schedule::command('app:import-product-images', ['--sheet', '--fresh'])
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

/**
 * Az importok külső URL-eket írnak a termékekre; ez tölti le őket a saját
 * tárhelyre, hogy a webshop ne a beszállítók szerveréről hotlinkeljen.
 */
Schedule::command('app:localize-product-images')
    ->weeklyOn(1, '04:00')
    ->withoutOverlapping()
    ->runInBackground();
