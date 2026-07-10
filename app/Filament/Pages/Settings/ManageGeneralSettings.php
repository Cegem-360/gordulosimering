<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Enums\NavigationGroup;
use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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
final class ManageGeneralSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    /** @var array<string, mixed> */
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Settings;

    protected static ?string $navigationLabel = 'Általános';

    protected static ?string $title = 'Általános beállítások';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.settings.manage-settings';

    public function mount(): void
    {
        $settings = resolve(GeneralSettings::class);

        $this->form->fill([
            'site_name' => $settings->site_name,
            'tagline' => $settings->tagline,
            'logo_light' => $settings->logo_light,
            'logo_dark' => $settings->logo_dark,
            'favicon' => $settings->favicon,
            'default_locale' => $settings->default_locale,
            'available_locales' => $settings->available_locales,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Alapadatok')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Website name')
                            ->required(),
                        TextInput::make('tagline')
                            ->label('Slogan'),
                    ]),
                Section::make('Megjelenés')
                    ->schema([
                        FileUpload::make('logo_light')
                            ->label('Logo (light)')
                            ->image()
                            ->directory('settings'),
                        FileUpload::make('logo_dark')
                            ->label('Logo (dark)')
                            ->image()
                            ->directory('settings'),
                        FileUpload::make('favicon')
                            ->image()
                            ->directory('settings'),
                    ])->columns(3),
                Section::make('Nyelvek')
                    ->schema([
                        Select::make('default_locale')
                            ->label('Default language')
                            ->options([
                                'hu' => 'Magyar',
                                'en' => 'English',
                                'de' => 'Deutsch',
                            ])
                            ->required(),
                        CheckboxList::make('available_locales')
                            ->label('Available languages')
                            ->options([
                                'hu' => 'Magyar',
                                'en' => 'English',
                                'de' => 'Deutsch',
                            ]),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = resolve(GeneralSettings::class);

        $settings->site_name = $data['site_name'];
        $settings->tagline = $data['tagline'] ?? '';
        $settings->logo_light = $data['logo_light'] ?? '';
        $settings->logo_dark = $data['logo_dark'] ?? '';
        $settings->favicon = $data['favicon'] ?? '';
        $settings->default_locale = $data['default_locale'];
        $settings->available_locales = $data['available_locales'] ?? [];

        $settings->save();

        Notification::make()->title('Beállítások mentve.')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->submit('save'),
        ];
    }
}
