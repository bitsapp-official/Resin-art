<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeSlideResource\Pages;
use App\Filament\Resources\HomeSlideResource\RelationManagers;
use App\Models\HomeSlide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HomeSlideResource extends Resource
{
    protected static ?string $model = HomeSlide::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Atelier & Content Pages';

    protected static ?string $navigationLabel = 'Hero Slides';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Slide Content & Typography')
                    ->schema([
                        Forms\Components\TextInput::make('tag')
                            ->label('Edition / Top Tag')
                            ->placeholder('e.g. HANDCRAFTED & MADE TO ORDER')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('title')
                            ->label('Headline Title')
                            ->placeholder('e.g. The quiet language of resin.')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Description Paragraph')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('link')
                            ->label('Button Target URL / Route')
                            ->placeholder('e.g. /collections/river-tables')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Slide Visuals & Settings')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Slide Artwork Image')
                            ->disk('public')
                            ->directory('gallery')
                            ->image()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Slide Order')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active on Homepage')
                            ->default(true)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Slide Image')
                    ->disk('public'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tag')
                    ->label('Tag')
                    ->color('gray')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListHomeSlides::route('/'),
            'create' => Pages\CreateHomeSlide::route('/create'),
            'edit' => Pages\EditHomeSlide::route('/{record}/edit'),
        ];
    }
}
