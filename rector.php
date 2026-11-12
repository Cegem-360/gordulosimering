<?php

declare(strict_types=1);

use Madbox99\RectorFilament\Set\FilamentSetList;
use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;

return RectorConfig::configure()
    ->withImportNames()
    ->withParallel()
    ->withComposerBased()
    ->withPhpSets()
    ->withEditorUrl('vscode://file/%file%:%line%')
    ->withSets([
        FilamentSetList::LARAVEL_APP,
    ])
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/tests',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/resources',
        __DIR__ . '/routes',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
    )
    ->withSkip([
        // Filament calls lifecycle hooks (afterCreate, afterSave, beforeSave, …) from its base classes
        // by name, so they must stay protected even on final pages, resources and importers.
        PrivatizeFinalClassMethodRector::class => ['*/app/Filament/*'],
    ]);
