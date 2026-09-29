<?php

declare(strict_types=1);

use Tests\TestCase;

it('shows the on-call rates, the delivery terms and the other services', function (): void {
    /** @var TestCase $this */
    $this->get(route('services'))->assertOk()
        ->assertSee(['Csapágy éjjel-nappal', 'Az üzlet nyitvatartásán túl üzletnyitás 2–3 órán belül.', '25 000 Ft + ÁFA'])
        ->assertDontSee(['20 000 Ft + ÁFA', '22 000 Ft + ÁFA', '28 000 Ft + ÁFA', 'házhoz is szállítjuk'])
        ->assertSee(['80 000 Ft felett', '100 000 Ft felett', '120 000 Ft felett'])
        ->assertSee(['7 990 Ft + ÁFA', '8 990 Ft + ÁFA', '9 990 Ft + ÁFA', '10 990 Ft + ÁFA', 'Legfeljebb 6 kg-os csomagig'])
        ->assertSee(['minőségi tanúsítványt', 'Ingyenes katalógusok', 'Oktatások szervezése', 'Mintadarabok bemutatása', 'Kártyaelfogadás'])
        ->assertSeeHtml('href="tel:+36309440203"');
});

it('gives gs@gordulo-simmering.hu as the email address', function (string $route): void {
    /** @var TestCase $this */
    $this->get(route($route))->assertOk()
        ->assertSeeHtml('href="mailto:gs@gordulo-simmering.hu"')
        ->assertDontSee('info@gordulo-simmering.hu');
})->with(['services', 'contact']);

it('links the homepage on-call card to the on-call section', function (): void {
    /** @var TestCase $this */
    $this->get('/')->assertOk()->assertSeeHtml('<a href="' . route('services') . '#ugyelet" class="group block">');
});
