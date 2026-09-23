<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * The top-level category that lists the brands we distribute. It is not a
     * product category, so the menus always show it last.
     */
    public const string BRAND_ROOT_NAME = 'FORGALMAZOTT MÁRKÁINK';

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

    /**
     * Top-level categories in menu order: alphabetical, with the brand list last.
     *
     * @param  Builder<Category>  $query
     */
    #[Scope]
    protected function menuRoots(Builder $query): void
    {
        $query->whereNull('category_id')
            ->orderByRaw('CASE WHEN name = ? THEN 1 ELSE 0 END', [self::BRAND_ROOT_NAME])
            ->orderBy('name');
    }
}
