<?php

declare(strict_types=1);

it('points the Integra7 connection at the ERP database, not the webshop database', function (): void {
    expect(config('database.connections.integra7'))
        ->host->toBe('185.111.89.210')
        ->database->toBe('integra7_simmeringws')
        ->username->toBe('integra7_simmeringread');
});
