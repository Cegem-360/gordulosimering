<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;

arch()->preset()->php();
// arch()->preset()->strict();
arch()->preset()->laravel();
arch()->preset()->security();
arch()->expect('App\Models')->toBeClasses()->toExtend(Model::class);
arch()->expect('App\Controllers\Controller')->toBeAbstract();
