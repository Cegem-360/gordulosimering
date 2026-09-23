<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Override;

#[Fillable([
    'name',
    'slug',
    'category_id',
    'sort_order',
    'description',
    'image',
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

    /**
     * Subcategories in menu order: see {@see self::ordered()}.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'category_id')->ordered();
    }

    public function isBrand(): bool
    {
        return $this->parentCategory?->name === self::BRAND_ROOT_NAME;
    }

    /**
     * Menu order: the position set in the admin, or the row order of the
     * category sheet for categories nobody has reordered, then by name.
     *
     * @param  Builder<Category>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderByRaw('CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * Top-level categories in menu order, with the brand list always last.
     *
     * @param  Builder<Category>  $query
     */
    #[Scope]
    protected function menuRoots(Builder $query): void
    {
        $query->whereNull('category_id')
            ->orderByRaw('CASE WHEN name = ? THEN 1 ELSE 0 END', [self::BRAND_ROOT_NAME])
            ->ordered();
    }

    /**
     * Public URL of the uploaded category photo, if there is one.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => blank($this->image) ? null : Storage::disk('public')->url($this->image));
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
