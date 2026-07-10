<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeoPages\Pages;

use App\Filament\Resources\SeoPages\SeoPageResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSeoPage extends CreateRecord
{
    protected static string $resource = SeoPageResource::class;
}
