<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Enums\NavigationGroup;
use App\Settings\PromotionSettings;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
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

/**
 * The announcement bar above the header and the homepage banner
 * (the client's request of 2026-11-12).
 *
 * @property-read Schema $form
 */
final class ManagePromotionSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    /**
     * A link is a path on the site ("/akciok") or a full web address.
     */
    public const string LINK_RULE = 'regex:/^(\/|https?:\/\/)\S*$/';

    /** @var array<string, mixed> */
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Settings;

    protected static ?string $navigationLabel = 'Sáv és banner';

    protected static ?string $title = 'Értesítési sáv és főoldali banner';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.settings.manage-settings';

    public function mount(): void
    {
        $settings = resolve(PromotionSettings::class);

        $this->form->fill($settings->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Felső értesítési sáv')
                    ->description('Minden oldal tetején, a fejléc felett látszik. Kikapcsolva nem marad helye.')
                    ->schema([
                        Toggle::make('announcement_enabled')
                            ->label('Sáv bekapcsolva')
                            ->live(),
                        TextInput::make('announcement_text')
                            ->label('Szöveg')
                            ->placeholder('Pl. December 24-én zárva tartunk.')
                            ->maxLength(250)
                            ->required(fn (callable $get): bool => (bool) $get('announcement_enabled'))
                            ->columnSpanFull(),
                        ColorPicker::make('announcement_color')
                            ->label('Háttérszín')
                            ->helperText('A betűszín magától fehér vagy fekete lesz, amelyik jobban olvasható.')
                            ->hex()
                            ->regex('/^#[0-9a-fA-F]{6}$/')
                            ->required(),
                        TextInput::make('announcement_url')
                            ->label('Link (nem kötelező)')
                            ->helperText('Pl. /akciok vagy https://… Üresen hagyva a sáv nem kattintható.')
                            ->rule(self::LINK_RULE)
                            ->maxLength(500),
                    ])->columns(2),
                Section::make('Főoldali banner')
                    ->description('A főoldalon, közvetlenül a fejléc alatt. Kikapcsolva a főoldal a megszokott módon jelenik meg.')
                    ->schema([
                        Toggle::make('banner_enabled')
                            ->label('Banner bekapcsolva')
                            ->live()
                            ->columnSpanFull(),
                        FileUpload::make('banner_desktop_image')
                            ->label('Asztali kép')
                            ->helperText('Javasolt méret: 1920 × 500 px (széles, kb. 4:1).')
                            ->image()
                            ->disk('public')
                            ->directory('banners')
                            ->maxSize(4096)
                            ->required(fn (callable $get): bool => (bool) $get('banner_enabled')),
                        FileUpload::make('banner_mobile_image')
                            ->label('Mobil kép (nem kötelező)')
                            ->helperText('Javasolt méret: 1080 × 1080 px (négyzetes). Ha üres, telefonon is az asztali kép látszik.')
                            ->image()
                            ->disk('public')
                            ->directory('banners')
                            ->maxSize(4096),
                        TextInput::make('banner_url')
                            ->label('Link (nem kötelező)')
                            ->helperText('Az akciós termék vagy kategória címe, pl. /termekkategoriak/csapagyak')
                            ->rule(self::LINK_RULE)
                            ->maxLength(500),
                        TextInput::make('banner_alt')
                            ->label('Kép leírása')
                            ->helperText('Felolvasóprogramoknak és keresőknek, pl. „Téli SKF akció -15%”.')
                            ->maxLength(200),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = resolve(PromotionSettings::class);

        $settings->announcement_enabled = (bool) $data['announcement_enabled'];
        $settings->announcement_text = filled($data['announcement_text'] ?? null) ? mb_trim((string) $data['announcement_text']) : null;
        $settings->announcement_color = (string) $data['announcement_color'];
        $settings->announcement_url = filled($data['announcement_url'] ?? null) ? mb_trim((string) $data['announcement_url']) : null;
        $settings->banner_enabled = (bool) $data['banner_enabled'];
        $settings->banner_desktop_image = $data['banner_desktop_image'] ?? null;
        $settings->banner_mobile_image = $data['banner_mobile_image'] ?? null;
        $settings->banner_url = filled($data['banner_url'] ?? null) ? mb_trim((string) $data['banner_url']) : null;
        $settings->banner_alt = filled($data['banner_alt'] ?? null) ? mb_trim((string) $data['banner_alt']) : null;

        $settings->save();

        Notification::make()->title('Beállítások mentve.')->success()->send();
    }
}
