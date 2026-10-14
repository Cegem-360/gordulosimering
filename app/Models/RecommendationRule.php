<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RecommendationRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * A "Gyakran együtt vásárolják" rule (client's list, 2026-10-14): products
 * bought from the source category (with its subcategories, or from anywhere
 * when it is empty) are offered products from the target category (with its
 * subcategories), or the products whose code starts with the target prefix.
 */
#[Fillable(['source_category_id', 'target_category_id', 'target_code_prefix', 'is_active'])]
final class RecommendationRule extends Model
{
    /** @use HasFactory<RecommendationRuleFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Category, $this>
     */
    public function sourceCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'source_category_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function targetCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'target_category_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
