<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountGroups\Pages;

use App\Filament\Resources\DiscountGroups\DiscountGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListDiscountGroups extends ListRecords
{
    protected static string $resource = DiscountGroupResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
