<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * "Értesítés küldése a vevőnek" is not a column: it tells the observer
     * whether this save emails the customer about a status change.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->record->sendsCustomerStatusEmail = (bool) ($data['notify_customer'] ?? true);
        unset($data['notify_customer']);

        return $data;
    }

    #[Override]
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return [...$data, 'notify_customer' => true];
    }

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
