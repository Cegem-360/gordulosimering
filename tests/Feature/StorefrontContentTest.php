<?php

declare(strict_types=1);

use Tests\TestCase;

it('does not list American Express among the accepted cards', function (): void {
    /** @var TestCase $this */
    $this->get(route('documents'))->assertOk()
        ->assertSee('MasterCard')
        ->assertDontSee('American Express');
});

it('does not show the brand logo strip on the homepage', function (): void {
    /** @var TestCase $this */
    $this->get('/')->assertOk()->assertDontSeeHtml('>Márkáink</h2>');
});
