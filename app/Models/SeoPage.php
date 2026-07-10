<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Madbox99\FilamentSeo\Concerns\HasSeo;
use Override;

#[Fillable(['title', 'slug', 'excerpt', 'content', 'featured_image', 'is_featured', 'is_published', 'published_at', 'sort_order'])]
#[Table(name: 'seo_pages')]
final class SeoPage extends Model
{
    use HasFactory;
    use HasSeo;

    #[Override]
    protected static function booted(): void
    {
        self::saving(function (SeoPage $page): void {
            if (blank($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true);
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }
}
