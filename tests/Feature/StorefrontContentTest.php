<?php

declare(strict_types=1);

use Tests\TestCase;

it('does not show the brand logo strip on the homepage', function (): void {
    /** @var TestCase $this */
    $this->get('/')->assertOk()->assertDontSeeHtml('>Márkáink</h2>');
});
