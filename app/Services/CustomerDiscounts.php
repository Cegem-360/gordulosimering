<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserDiscount;
use Illuminate\Support\Facades\Auth;

/**
 * A bejelentkezett vevő kedvezménye egy termékcsoportra (Csoportkód): az alap
 * kedvezménye és az adott csoportra kapott kedvezmény közül a nagyobb.
 * Vendégnek nincs vevőkedvezménye. Kérésenként egyszer tölti be a vevő
 * kedvezményeit, hogy a terméklisták ne kérdezzék le termékenként.
 */
final class CustomerDiscounts
{
    /** @var array<int, array{base: float, groups: array<string, float>}> */
    private array $loaded = [];

    public function percentageFor(?string $groupCode): float
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return 0.0;
        }

        $discounts = $this->loaded[$user->getKey()] ??= $this->load($user);

        $percentage = max($discounts['base'], $discounts['groups'][(string) $groupCode] ?? 0.0);

        return min(max($percentage, 0.0), 100.0);
    }

    /**
     * @return array{base: float, groups: array<string, float>}
     */
    private function load(User $user): array
    {
        return [
            'base' => (float) $user->base_discount_percentage,
            'groups' => $user->discounts()
                ->with('discountGroup:id,code')
                ->get()
                ->mapWithKeys(fn (UserDiscount $discount): array => [
                    $discount->discountGroup->code => (float) $discount->percentage,
                ])
                ->all(),
        ];
    }
}
