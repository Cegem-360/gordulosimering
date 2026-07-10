<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Enums\NavigationGroup;
use App\Settings\SocialSettings;
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
final class ManageSocialSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    /** @var array<string, mixed> */
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Settings;

    protected static ?string $navigationLabel = 'Social média';

    protected static ?string $title = 'Social média linkek';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.settings.manage-settings';

    public function mount(): void
    {
        $settings = resolve(SocialSettings::class);

        $this->form->fill([
            'facebook' => $settings->facebook,
            'instagram' => $settings->instagram,
            'twitter' => $settings->twitter,
            'linkedin' => $settings->linkedin,
            'youtube' => $settings->youtube,
            'tiktok' => $settings->tiktok,
            'pinterest' => $settings->pinterest,
            'threads' => $settings->threads,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('facebook'),
                        TextInput::make('instagram'),
                        TextInput::make('twitter')->label('X (Twitter)'),
                        TextInput::make('linkedin')->label('LinkedIn'),
                        TextInput::make('youtube')->label('YouTube'),
                        TextInput::make('tiktok')->label('TikTok'),
                        TextInput::make('pinterest'),
                        TextInput::make('threads'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = resolve(SocialSettings::class);

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
