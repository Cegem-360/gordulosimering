<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;

it('schedules the product image import once a week', function (): void {
    $event = collect(resolve(Schedule::class)->events())
        ->first(fn (Event $event): bool => str_contains($event->command ?? '', 'app:import-product-images'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 3 * * 1')
        ->and($event->command)->toContain('--sheet')
        ->and($event->command)->toContain('--fresh');
});

it('schedules the type image import weekly, after the code based image import', function (): void {
    $events = collect(resolve(Schedule::class)->events());

    $typeImages = $events->first(fn (Event $event): bool => str_contains($event->command ?? '', 'app:import-type-images'));

    expect($typeImages)->not->toBeNull()
        ->and($typeImages->expression)->toBe('30 3 * * 1');
});

it('downloads the imported images weekly, after both image imports', function (): void {
    $event = collect(resolve(Schedule::class)->events())
        ->first(fn (Event $event): bool => str_contains($event->command ?? '', 'app:localize-product-images'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 4 * * 1');
});

it('works through the queued mails every minute, since no queue worker runs on the server', function (): void {
    $event = collect(resolve(Schedule::class)->events())
        ->first(fn (Event $event): bool => str_contains($event->command ?? '', 'queue:work'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('* * * * *')
        ->and($event->command)->toContain('--stop-when-empty');
});
