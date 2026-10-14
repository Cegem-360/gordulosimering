<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationRules;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RecommendationRules\Pages\CreateRecommendationRule;
use App\Filament\Resources\RecommendationRules\Pages\EditRecommendationRule;
use App\Filament\Resources\RecommendationRules\Pages\ListRecommendationRules;
use App\Filament\Resources\RecommendationRules\Schemas\RecommendationRuleForm;
use App\Filament\Resources\RecommendationRules\Tables\RecommendationRulesTable;
use App\Models\Category;
use App\Models\RecommendationRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

/**
 * The rules behind the product page's "Gyakran együtt vásárolják" row.
 */
final class RecommendationRuleResource extends Resource
{
    protected static ?string $model = RecommendationRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Webshop;

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Ajánlások';

    protected static ?string $modelLabel = 'ajánlás';

    protected static ?string $pluralModelLabel = 'Ajánlások (gyakran együtt vásárolják)';

    /** @var array<int, string>|null */
    private static ?array $categoryPaths = null;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return RecommendationRuleForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return RecommendationRulesTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListRecommendationRules::route('/'),
            'create' => CreateRecommendationRule::route('/create'),
            'edit' => EditRecommendationRule::route('/{record}/edit'),
        ];
    }

    /**
     * Every category as "MAIN › SUB › SUB", sorted by that path.
     *
     * @return array<int, string>
     */
    public static function categoryPaths(): array
    {
        if (self::$categoryPaths !== null) {
            return self::$categoryPaths;
        }

        $categories = Category::query()->get(['id', 'name', 'category_id'])->keyBy('id');
        $paths = [];

        foreach ($categories as $category) {
            $names = [];
            $current = $category;

            for ($depth = 0; $current !== null && $depth < 10; $depth++) {
                array_unshift($names, $current->name);
                $current = $categories->get($current->category_id);
            }

            $paths[$category->id] = implode(' › ', $names);
        }

        asort($paths, SORT_NATURAL | SORT_FLAG_CASE);

        return self::$categoryPaths = $paths;
    }
}
