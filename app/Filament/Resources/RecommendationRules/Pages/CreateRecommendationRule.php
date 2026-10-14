<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationRules\Pages;

use App\Filament\Resources\RecommendationRules\RecommendationRuleResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRecommendationRule extends CreateRecord
{
    protected static string $resource = RecommendationRuleResource::class;
}
