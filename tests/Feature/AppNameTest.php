<?php

declare(strict_types=1);

/*
 * `config('app.name')` is the browser tab title on the front end and in the admin
 * panel, the `application-name` meta tag, the footer of every transactional mail
 * and the default MAIL_FROM_NAME. It shipped as "Laravel". The real .env is not
 * version controlled, so .env.example is what pins the default for a new checkout.
 */

it('ships the company name as the default app name', function (): void {
    expect(file_get_contents(base_path('.env.example')))
        ->toContain('APP_NAME="Gördülő Simering Kft"')
        ->not->toContain('APP_NAME=Laravel');
});

it('uses the company name as the configured app name', function (): void {
    expect(config('app.name'))->toBe('Gördülő Simering Kft');
});
