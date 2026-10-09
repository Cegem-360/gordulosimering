<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Schemas\ProductForm;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ProductForm::saveDocumentRows($data);
    }
}
