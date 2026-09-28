<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountGroups\Pages;

use App\Filament\Resources\DiscountGroups\DiscountGroupResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateDiscountGroup extends CreateRecord
{
    protected static string $resource = DiscountGroupResource::class;
}
