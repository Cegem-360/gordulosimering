<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class ContactSettings extends Settings
{
    public string $company_name;

    public string $address;

    public string $city;

    public string $zip_code;

    public string $country;

    public string $phone;

    public string $email;

    public string $tax_number;

    public string $registration_number;

    public string $google_maps_embed;

    public static function group(): string
    {
        return 'contact';
    }
}
