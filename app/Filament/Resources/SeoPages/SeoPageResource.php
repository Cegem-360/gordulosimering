<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeoPages;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SeoPages\Pages\CreateSeoPage;
use App\Filament\Resources\SeoPages\Pages\EditSeoPage;
use App\Filament\Resources\SeoPages\Pages\ListSeoPages;
use App\Filament\Support\FieldLabel;
use App\Models\SeoPage;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Madbox99\FilamentSeo\Forms\SeoFields;
use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels;
use Override;
use UnitEnum;

final class SeoPageResource extends Resource
{
    use TranslatesFilamentModelLabels;

    protected static ?string $model = SeoPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Content;

    protected static ?string $navigationLabel = 'SEO oldalak';

    protected static ?int $navigationSort = 6;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state): mixed => $set('slug', Str::slug($state ?? ''))),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Az oldal URL-je: /{slug}'),
                        Textarea::make('excerpt')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('featured_image')
                            ->image()
                            ->disk('public')
                            ->directory('seo-pages')
                            ->visibility('public'),
                        DateTimePicker::make('published_at'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_published'),
                        Toggle::make('is_featured'),
                    ])->columns(2),
                SeoFields::make()
                    ->schema([
                        ...self::translatedSeoFields(),
                        Select::make('og_type')
                            ->options([
                                'article' => 'article',
                                'website' => 'website',
                            ])
                            ->default('article'),
                        Textarea::make('schema_markup')
                            ->rows(5)
                            ->helperText('Opcionális, érvényes JSON. Automatikus structured data mellé.')
                            ->rules(['nullable', 'json'])
                            ->formatStateUsing(fn ($state): ?string => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $state)
                            ->dehydrateStateUsing(fn (?string $state): mixed => filled($state) ? json_decode($state, true) : null),
                    ]),
            ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                IconColumn::make('is_published')
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->dateTime('Y. m. d.')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_published'),
                TernaryFilter::make('is_featured'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListSeoPages::route('/'),
            'create' => CreateSeoPage::route('/create'),
            'edit' => EditSeoPage::route('/{record}/edit'),
        ];
    }

    /**
     * The SEO package ships its fields with hardcoded English labels, which
     * the global default-label resolver cannot override. Relabel them here,
     * keeping the package's own validation rules.
     *
     * @return array<Field>
     */
    private static function translatedSeoFields(): array
    {
        return array_map(
            fn (Field $field): Field => $field->label(
                FieldLabel::for('seo.' . $field->getName()) ?? $field->getLabel(),
            ),
            SeoFields::schema(),
        );
    }
}
