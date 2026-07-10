<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Enums\NavigationGroup;
use App\Settings\ContactSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** @property-read Schema $form */
final class ManageContactSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    /** @var array<string, mixed> */
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Settings;

    protected static ?string $navigationLabel = 'Kapcsolat / Cégadatok';

    protected static ?string $title = 'Kapcsolat és cégadatok';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.settings.manage-settings';

    public function mount(): void
    {
        $settings = resolve(ContactSettings::class);

        $this->form->fill([
            'company_name' => $settings->company_name,
            'address' => $settings->address,
            'city' => $settings->city,
            'zip_code' => $settings->zip_code,
            'country' => $settings->country,
            'phone' => $settings->phone,
            'email' => $settings->email,
            'tax_number' => $settings->tax_number,
            'registration_number' => $settings->registration_number,
            'google_maps_embed' => $settings->google_maps_embed,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Cégadatok')
                    ->schema([
                        TextInput::make('company_name'),
                        TextInput::make('tax_number'),
                        TextInput::make('registration_number')->label('Company registration number'),
                    ])->columns(3),
                Section::make('Cím')
                    ->schema([
                        TextInput::make('address')->label('Street address'),
                        TextInput::make('city'),
                        TextInput::make('zip_code')->label('Postal code'),
                        TextInput::make('country'),
                    ])->columns(2),
                Section::make('Elérhetőség')
                    ->schema([
                        TextInput::make('phone')->tel(),
                        TextInput::make('email')->email(),
                    ])->columns(2),
                Section::make('Térkép')
                    ->schema([
                        Textarea::make('google_maps_embed')->label('Google Maps embed code')->rows(3),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = resolve(ContactSettings::class);

        foreach ($data as $key => $value) {
            $settings->{$key} = $value ?? '';
        }

        $settings->save();

        Notification::make()->title('Beállítások mentve.')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->submit('save'),
        ];
    }
}
