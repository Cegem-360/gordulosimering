<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\IntegraKnownProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Az Integra7 egyszer már látott termékkódjai. Az Integra7Syncer ebből tudja,
 * melyik kód új az ERP-ben: csak ezekből hoz létre (rejtett) terméket, a
 * webshopból szándékosan kihagyott régi termékekből nem.
 */
#[Fillable([
    'code',
])]
final class IntegraKnownProduct extends Model
{
    /** @use HasFactory<IntegraKnownProductFactory> */
    use HasFactory;
}
