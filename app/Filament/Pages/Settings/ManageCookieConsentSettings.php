<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Enums\NavigationGroup;
use App\Settings\CookieConsentSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** @property-read Schema $form */
final class ManageCookieConsentSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    /** @var array<string, mixed> */
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Settings;

    protected static ?string $navigationLabel = 'Cookie Consent';

    protected static ?string $title = 'Cookie Consent beállítások';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.settings.manage-settings';

    public function mount(): void
    {
        $settings = resolve(CookieConsentSettings::class);

        $this->form->fill([
            'enabled' => $settings->enabled,
            'title' => $settings->title,
            'description' => $settings->description,
            'accept_button_text' => $settings->accept_button_text,
            'reject_button_text' => $settings->reject_button_text,
            'settings_button_text' => $settings->settings_button_text,
            'categories' => $settings->categories,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Általános')
                    ->schema([
                        Toggle::make('enabled')
                            ->label('Cookie banner enabled'),
                        TextInput::make('title')
                            ->required(),
                        Textarea::make('description')
                            ->rows(3),
                    ]),
                Section::make('Gombok')
                    ->schema([
                        TextInput::make('accept_button_text')->label('Accept button'),
                        TextInput::make('reject_button_text')->label('Reject button'),
                        TextInput::make('settings_button_text')->label('Settings button'),
                    ])->columns(3),
                Section::make('Kategóriák')
                    ->schema([
                        Repeater::make('categories')
                            ->label('')
                            ->schema([
                                TextInput::make('name')->required(),
                                TextInput::make('key')->required()->alphaDash(),
                                Textarea::make('description')->rows(2),
                                Toggle::make('required'),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = resolve(CookieConsentSettings::class);

        $settings->enabled = $data['enabled'] ?? true;
        $settings->title = $data['title'] ?? '';
        $settings->description = $data['description'] ?? '';
        $settings->accept_button_text = $data['accept_button_text'] ?? '';
        $settings->reject_button_text = $data['reject_button_text'] ?? '';
        $settings->settings_button_text = $data['settings_button_text'] ?? '';
        $settings->categories = $data['categories'] ?? [];

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
