<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Enums\NavigationGroup;
use App\Enums\VatRate;
use App\Settings\ShopSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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
final class ManageShopSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    /** @var array<string, mixed> */
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Settings;

    protected static ?string $navigationLabel = 'Webshop';

    protected static ?string $title = 'Webshop beállítások';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.settings.manage-settings';

    public function mount(): void
    {
        $settings = resolve(ShopSettings::class);

        $this->form->fill([
            'pricing_mode' => $settings->pricing_mode,
            'track_inventory' => $settings->track_inventory,
            'currency' => $settings->currency,
            'default_vat_rate' => $settings->default_vat_rate,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Árazás')
                    ->schema([
                        Select::make('pricing_mode')
                            ->options([
                                'gross' => 'Bruttó (a megadott ár az ÁFÁ-t tartalmazza)',
                                'net' => 'Nettó (az ÁFÁ-t felszámoljuk)',
                            ])
                            ->required(),
                        Select::make('default_vat_rate')
                            ->label('Default VAT rate')
                            ->options(collect(VatRate::cases())->mapWithKeys(fn (VatRate $v): array => [$v->value => $v->label()]))
                            ->required(),
                        TextInput::make('currency')
                            ->required()
                            ->maxLength(3),
                    ])->columns(3),
                Section::make('Készlet')
                    ->schema([
                        Toggle::make('track_inventory')
                            ->label('Stock tracking enabled'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = resolve(ShopSettings::class);

        $settings->pricing_mode = $data['pricing_mode'];
        $settings->track_inventory = (bool) $data['track_inventory'];
        $settings->currency = $data['currency'];
        $settings->default_vat_rate = $data['default_vat_rate'];

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
