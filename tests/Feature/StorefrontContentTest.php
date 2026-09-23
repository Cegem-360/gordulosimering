<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\ShippingMethod;
use Tests\TestCase;

it('does not list American Express among the accepted cards', function (): void {
    /** @var TestCase $this */
    $this->get(route('documents'))->assertOk()
        ->assertSee('MasterCard')
        ->assertDontSee('American Express');
});

it('offers personal pickup as a free shipping method', function (): void {
    $pickup = ShippingMethod::query()->where('name', 'pickup')->first();

    expect($pickup)->not->toBeNull()
        ->and($pickup->title)->toBe('Személyes átvétel')
        ->and($pickup->cost)->toBe(0);
});

it('renames the bearing housings root category and leaves other categories alone', function (): void {
    $housings = Category::query()->create(['name' => 'CSAPÁGYHÁZAK (S1,S2,S3,S4,S5)', 'slug' => 'csapagyhazak-s1s2s3s4s5']);
    $child = Category::query()->create(['name' => 'CSAPÁGYHÁZAK (S1,S2,S3,S4,S5)', 'slug' => 'nested', 'category_id' => $housings->id]);

    $migration = require database_path('migrations/2026_09_23_143238_rename_bearing_housings_category.php');
    $migration->up();

    expect($housings->fresh())->name->toBe('CSAPÁGYHÁZAK')->slug->toBe('csapagyhazak')
        ->and($child->fresh()->name)->toBe('CSAPÁGYHÁZAK (S1,S2,S3,S4,S5)');

    $migration->down();

    expect($housings->fresh())->name->toBe('CSAPÁGYHÁZAK (S1,S2,S3,S4,S5)')->slug->toBe('csapagyhazak-s1s2s3s4s5');
});

it('does not show the brand logo strip on the homepage', function (): void {
    /** @var TestCase $this */
    $this->get('/')->assertOk()->assertDontSeeHtml('>Márkáink</h2>');
});
