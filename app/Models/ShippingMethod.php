<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable([
    'id',
    'name',
    'title',
    'slug',
    'description',
    'cost',
])]
final class ShippingMethod extends Model
{
    use HasFactory;

    #[Override]
    protected function casts(): array
    {
        return [
            'cost' => 'int',
        ];
    }
}
