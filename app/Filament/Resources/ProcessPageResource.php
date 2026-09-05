<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcessPageResource\Pages;
use App\Filament\Resources\ProcessPageResource\RelationManagers;
use App\Models\ProcessPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Enums\ProcessPageStatus;

class ProcessPageResource extends Resource
{
    protected static ?string $model = ProcessPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Atelier & Content Pages';

    protected static ?string $navigationLabel = 'Our Process Page';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Hero Header')
                            ->schema([
                                Forms\Components\TextInput::make('eyebrow')
                                    ->default('OUR PROCESS')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('title')
                                    ->default('Six weeks, one object.')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('description')
                                    ->default('From timber selection to the final hand-polish, nothing here is hurried.')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Section::make('Process Steps (Drag to Reorder)')
                            ->description('Add, edit, deactivate, or drag-and-drop steps into the desired order.')
                            ->schema([
                                Forms\Components\Repeater::make('steps')
                                    ->relationship('steps')
                                    ->orderColumn('sort_order')
                                    ->defaultItems(6)
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('step_number')
                                                    ->label('Step Number (e.g. 01)')
                                                    ->placeholder('Auto-numbered if blank'),

                                                Forms\Components\TextInput::make('title')
                                                    ->required()
                                                    ->columnSpan(2),
                                            ]),

                                        Forms\Components\Textarea::make('description')
                                            ->required()
                                            ->rows(2)
                                            ->columnSpanFull(),

                                        Forms\Components\FileUpload::make('image_path')
                                            ->label('Process Visual Image')
                                            ->disk('public')
                                            ->directory('process')
                                            ->visibility('public')
                                            ->image()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->maxSize(5120)
                                            ->columnSpanFull(),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('image_alt')
                                                    ->label('Image Alt Text'),

                                                Forms\Components\TextInput::make('image_caption')
                                                    ->label('Image Caption (Optional)'),
                                            ]),

                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Step Active')
                                            ->default(true),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => ($state['step_number'] ?? '') . ' — ' . ($state['title'] ?? 'Step'))
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Custom Order Call To Action (CTA)')
                            ->schema([
                                Forms\Components\TextInput::make('cta_title')
                                    ->default('Have a custom piece in mind?')
                                    ->required(),

                                Forms\Components\TextInput::make('cta_button_text')
                                    ->default('SUBMIT YOUR REQUIREMENTS')
                                    ->required(),

                                Forms\Components\TextInput::make('cta_url')
                                    ->default('/custom')
                                    ->required()
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status & Publishing')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options(ProcessPageStatus::class)
                                    ->default(ProcessPageStatus::PUBLISHED->value)
                                    ->required(),
                            ]),

                        Forms\Components\Section::make('SEO Metadata')
                            ->schema([
                                Forms\Components\TextInput::make('seo_title')
                                    ->label('Meta Title')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('seo_description')
                                    ->label('Meta Description')
                                    ->rows(4),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('eyebrow')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('title')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof ProcessPageStatus ? $state->value : $state) {
                        'PUBLISHED' => 'success',
                        'DRAFT' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('steps_count')
                    ->counts('steps')
                    ->label('Total Steps'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M d, Y H:i')
                    ->label('Last Modified'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('viewProcess')
                    ->label('View Public Page')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (): string => '/our-process', shouldOpenInNewTab: true)
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProcessPages::route('/'),
            'create' => Pages\CreateProcessPage::route('/create'),
            'edit' => Pages\EditProcessPage::route('/manage'),
        ];
    }
}
