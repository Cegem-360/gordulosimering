<?php

declare(strict_types=1);

use App\Livewire\CheckOut;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CheckOut::class)
        ->assertStatus(200);
});
