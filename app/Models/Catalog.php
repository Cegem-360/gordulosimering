<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CatalogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Override;

/**
 * A downloadable catalogue (PDF) on the Katalógusok page.
 */
#[Fillable(['title', 'description', 'file', 'is_active', 'sort_order'])]
final class Catalog extends Model
{
    /** @use HasFactory<CatalogFactory> */
    use HasFactory;

    public function fileUrl(): string
    {
        return Storage::disk('public')->url($this->file);
    }

    /**
     * The active catalogues in the admin's order.
     */
    #[Scope]
    protected function listed(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
