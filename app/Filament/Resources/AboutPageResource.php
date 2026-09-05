<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AboutPageResource\Pages;
use App\Models\AboutPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AboutPageResource extends Resource
{
    protected static ?string $model = AboutPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Atelier & Content Pages';

    protected static ?string $navigationLabel = 'About Atelier Page';

    protected static ?int $navigationSort = 6;

    public static function canCreate(): bool
    {
        return AboutPage::count() === 0;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('About Page Content')
                    ->tabs([
                        
                        // Tab 1: Hero & Story Narrative
                        Forms\Components\Tabs\Tab::make('Hero & Story Narrative')
                            ->icon('heroicon-o-book-open')
                            ->schema([
                                Forms\Components\Section::make('Hero Header')
                                    ->description('Main headline, eyebrow, and featured atelier image at top of About page.')
                                    ->schema([
                                        Forms\Components\TextInput::make('eyebrow')
                                            ->label('Header Eyebrow')
                                            ->default('THE HOUSE · EST. 2013')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('hero_title')
                                            ->label('Hero Title')
                                            ->default('A quiet atelier.')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\Textarea::make('hero_description')
                                            ->label('Brand Story Intro Paragraph')
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        Forms\Components\FileUpload::make('hero_image')
                                            ->label('Featured Atelier Hero Image')
                                            ->disk('public')
                                            ->directory('about')
                                            ->visibility('public')
                                            ->image()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->maxSize(5120)
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('hero_image_alt')
                                            ->label('Hero Image Alt Text (SEO & Accessibility)')
                                            ->columnSpanFull(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Founder Quote Block')
                                    ->description('Editorial quote overlay card rendered on top of the hero atelier image.')
                                    ->schema([
                                        Forms\Components\Textarea::make('founder_quote')
                                            ->label('Founder Quote Text')
                                            ->placeholder('"We pour slowly. That is really the whole philosophy."')
                                            ->rows(2)
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('founder_name')
                                            ->label('Founder Citation / Name')
                                            ->placeholder('— ELÈNE MARCHAND, FOUNDER')
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make('Our Story & Sustainable Craft')
                                    ->description('Deep brand narrative, wood selection, pigment tinting, and sustainable craft details.')
                                    ->schema([
                                        Forms\Components\TextInput::make('story_eyebrow')
                                            ->label('Story Eyebrow')
                                            ->default('OUR STORY')
                                            ->required(),

                                        Forms\Components\TextInput::make('story_title')
                                            ->label('Story Title')
                                            ->default('Twelve years, one rhythm.')
                                            ->required(),

                                        Forms\Components\Textarea::make('story_content')
                                            ->label('Detailed Story Content')
                                            ->rows(5)
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('materials_content')
                                            ->label('Materials & Responsible Sourcing Description')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),

                        // Tab 2: Chronicle Timeline
                        Forms\Components\Tabs\Tab::make('Chronicle Timeline')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Forms\Components\Section::make('Historical Milestones (Drag to Reorder)')
                                    ->description('Chronological story timeline steps (e.g. 2016 Kitchen table, 2018 Atelier...).')
                                    ->schema([
                                        Forms\Components\Repeater::make('timelineSteps')
                                            ->relationship('timelineSteps')
                                            ->orderColumn('sort_order')
                                            ->defaultItems(5)
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('year')
                                                            ->label('Year (e.g. 2016)')
                                                            ->required(),

                                                        Forms\Components\TextInput::make('title')
                                                            ->label('Milestone Title')
                                                            ->required()
                                                            ->columnSpan(2),
                                                    ]),

                                                Forms\Components\Textarea::make('description')
                                                    ->label('Milestone Narrative')
                                                    ->required()
                                                    ->rows(2)
                                                    ->columnSpanFull(),

                                                Forms\Components\Toggle::make('is_active')
                                                    ->label('Milestone Active')
                                                    ->default(true),
                                            ])
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => ($state['year'] ?? '') . ' — ' . ($state['title'] ?? 'Milestone'))
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 3: Editorial Craft Values
                        Forms\Components\Tabs\Tab::make('Editorial Craft Values')
                            ->icon('heroicon-o-check-badge')
                            ->schema([
                                Forms\Components\Section::make('Editorial Craft Values (Drag to Reorder)')
                                    ->description('Brand values grid (e.g. 01 Slow, 02 Honest, 03 Quiet).')
                                    ->schema([
                                        Forms\Components\Repeater::make('values')
                                            ->relationship('values')
                                            ->orderColumn('sort_order')
                                            ->defaultItems(3)
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('number')
                                                            ->label('Number (e.g. 01)')
                                                            ->required(),

                                                        Forms\Components\TextInput::make('title')
                                                            ->label('Value Title')
                                                            ->required()
                                                            ->columnSpan(2),
                                                    ]),

                                                Forms\Components\Textarea::make('description')
                                                    ->label('Value Description')
                                                    ->required()
                                                    ->rows(2)
                                                    ->columnSpanFull(),

                                                Forms\Components\Toggle::make('is_active')
                                                    ->label('Value Active')
                                                    ->default(true),
                                            ])
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => ($state['number'] ?? '') . ' — ' . ($state['title'] ?? 'Value'))
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 4: The Artisans / Craft Team
                        Forms\Components\Tabs\Tab::make('The Artisans ("The hands.")')
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                Forms\Components\Section::make('Artisans & Team Members (Drag to Reorder)')
                                    ->description('Artisan team member cards displayed under "The hands." section.')
                                    ->schema([
                                        Forms\Components\Repeater::make('artisans')
                                            ->relationship('artisans')
                                            ->orderColumn('sort_order')
                                            ->defaultItems(3)
                                            ->schema([
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Artisan Name')
                                                            ->required(),

                                                        Forms\Components\TextInput::make('role')
                                                            ->label('Artisan Role / Subtitle')
                                                            ->placeholder('FOUNDER · MASTER POURER')
                                                            ->required(),
                                                    ]),

                                                Forms\Components\FileUpload::make('image_path')
                                                    ->label('Artisan Photo / Visual')
                                                    ->disk('public')
                                                    ->directory('about')
                                                    ->visibility('public')
                                                    ->image()
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                                    ->maxSize(5120)
                                                    ->columnSpanFull(),

                                                Forms\Components\Toggle::make('is_active')
                                                    ->label('Artisan Active')
                                                    ->default(true),
                                            ])
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => ($state['name'] ?? 'Artisan') . ' — ' . ($state['role'] ?? 'Role'))
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 5: Publishing & SEO Metadata
                        Forms\Components\Tabs\Tab::make('Publishing & SEO')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Forms\Components\Section::make('Publishing Status & SEO Metadata')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_published')
                                            ->label('Published (Visible to Public)')
                                            ->default(true),

                                        Forms\Components\TextInput::make('seo_title')
                                            ->label('SEO Meta Title')
                                            ->placeholder('About Us — Maison Résine Atelier'),

                                        Forms\Components\Textarea::make('seo_description')
                                            ->label('SEO Meta Description')
                                            ->rows(3),
                                    ]),
                            ]),

                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('hero_image')
                    ->disk('public')
                    ->square()
                    ->label('Hero Image'),

                Tables\Columns\TextColumn::make('hero_title')
                    ->label('Hero Title')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('story_title')
                    ->label('Story Title')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('previewPage')
                    ->label('View About Page')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url('/about', shouldOpenInNewTab: true)
                    ->color('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAboutPages::route('/'),
            'create' => Pages\CreateAboutPage::route('/create'),
            'edit' => Pages\EditAboutPage::route('/manage'),
        ];
    }
}
