<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroup: string implements HasLabel
{
    case Webshop = 'Webshop';
    case Content = 'Content';
    case Marketing = 'Marketing';
    case Forms = 'Forms';
    case Media = 'Media';
    case Seo = 'SEO';
    case Settings = 'Settings';
    case Users = 'Users';

    public function getLabel(): string
    {
        return match ($this) {
            self::Webshop => __('Webshop'),
            self::Content => __('Content'),
            self::Marketing => __('Marketing'),
            self::Forms => __('Forms'),
            self::Media => __('Media'),
            self::Seo => __('SEO'),
            self::Settings => __('Settings'),
            self::Users => __('Users'),
        };
    }
}
