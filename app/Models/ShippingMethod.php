<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'title',
        'slug',
        'description',
        'cost',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'int',
        ];
    }
}
