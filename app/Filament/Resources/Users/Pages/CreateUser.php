<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Pages\Concerns\ManagesUserGroupDiscounts;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateUser extends CreateRecord
{
    use ManagesUserGroupDiscounts;

    protected static string $resource = UserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->pullGroupDiscounts($data);
    }

    protected function afterCreate(): void
    {
        $this->syncGroupDiscounts();
    }
}
