<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Enums\NavigationGroup;
use App\Settings\IntegrationSettings;
use BackedEnum;
use Filament\Actions\Action;
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
final class ManageIntegrationSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    /** @var array<string, mixed> */
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Settings;

    protected static ?string $navigationLabel = 'Integrációk';

    protected static ?string $title = 'Integrációk';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.settings.manage-settings';

    public function mount(): void
    {
        $settings = resolve(IntegrationSettings::class);

        $this->form->fill([
            'google_analytics_id' => $settings->google_analytics_id,
            'google_tag_manager_id' => $settings->google_tag_manager_id,
            'facebook_pixel_id' => $settings->facebook_pixel_id,
            'hotjar_id' => $settings->hotjar_id,
            'linkedin_insight_id' => $settings->linkedin_insight_id,
            'chat_plugin_key' => $settings->chat_plugin_key,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Google')
                    ->schema([
                        TextInput::make('google_analytics_id')->label('Google Analytics 4 ID')->placeholder('G-XXXXXXXXXX'),
                        TextInput::make('google_tag_manager_id')->label('Google Tag Manager ID')->placeholder('GTM-XXXXXXX'),
                    ])->columns(2),
                Section::make('Marketing')
                    ->schema([
                        TextInput::make('facebook_pixel_id')->label('Facebook Pixel ID'),
                        TextInput::make('hotjar_id')->label('Hotjar Site ID'),
                        TextInput::make('linkedin_insight_id')->label('LinkedIn Insight Tag ID'),
                    ])->columns(2),
                Section::make('Egyéb')
                    ->schema([
                        TextInput::make('chat_plugin_key')->label('Cégem 360 AI Chat Plugin key'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = resolve(IntegrationSettings::class);

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
