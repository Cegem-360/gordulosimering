<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages\Concerns;

use App\Models\DiscountGroup;
use App\Models\UserDiscount;

/**
 * A user űrlapján minden termékcsoport (Csoportkód) egy százalék-mezőként
 * jelenik meg, a `group_discounts` kulcs alatt. Betöltéskor feltölti a vevő
 * meglévő kedvezményeivel, mentéskor pedig szinkronizálja: a kitöltött
 * értékek mentődnek, az üres vagy 0 értékek törlődnek. A mezők a
 * discount_groups táblából épülnek, így új csoport magától megjelenik, a
 * megszűnt pedig eltűnik.
 */
trait ManagesUserGroupDiscounts
{
    /** @var array<string, mixed> */
    protected array $groupDiscounts = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $existing = ($this->record ?? null)
            ? $this->record->discounts()
                ->with('discountGroup:id,code')
                ->get()
                ->mapWithKeys(fn (UserDiscount $discount): array => [$discount->discountGroup->code => $discount->percentage])
                ->all()
            : [];

        $data['group_discounts'] = DiscountGroup::query()
            ->orderBy('code')
            ->pluck('code')
            ->mapWithKeys(fn (string $code): array => [$code => $existing[$code] ?? null])
            ->all();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function pullGroupDiscounts(array $data): array
    {
        $this->groupDiscounts = $data['group_discounts'] ?? [];
        unset($data['group_discounts']);

        return $data;
    }

    protected function syncGroupDiscounts(): void
    {
        $groupIds = DiscountGroup::query()->pluck('id', 'code');

        foreach ($this->groupDiscounts as $code => $percentage) {
            $groupId = $groupIds[$code] ?? null;

            if ($groupId === null) {
                continue;
            }

            if (blank($percentage) || (float) $percentage <= 0) {
                $this->record->discounts()->where('discount_group_id', $groupId)->delete();

                continue;
            }

            $this->record->discounts()->updateOrCreate(
                ['discount_group_id' => $groupId],
                ['percentage' => $percentage],
            );
        }
    }
}
