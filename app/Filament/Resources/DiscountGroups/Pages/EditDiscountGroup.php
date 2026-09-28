<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountGroups\Pages;

use App\Filament\Resources\DiscountGroups\DiscountGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditDiscountGroup extends EditRecord
{
    protected static string $resource = DiscountGroupResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
