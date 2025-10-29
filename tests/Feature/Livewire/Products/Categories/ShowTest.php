<?php

use App\Livewire\Products\Categories\Show;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Show::class)
        ->assertStatus(200);
});
