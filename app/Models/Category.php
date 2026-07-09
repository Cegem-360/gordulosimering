<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'category_id',
    'description',
    'display',
])]
#[Table(name: 'product_categories')]
final class Category extends Model
{
    use HasFactory;

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(self::class, 'category_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'category_id');
    }
}
