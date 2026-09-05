<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactPageContentResource\Pages;
use App\Filament\Resources\ContactPageContentResource\RelationManagers;
use App\Models\ContactPageContent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactPageContentResource extends Resource
{
    protected static ?string $model = ContactPageContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Atelier & Content Pages';

    protected static ?string $navigationLabel = 'Contact Page Details';

    protected static ?int $navigationSort = 8;

    public static function canCreate(): bool
    {
        return ContactPageContent::count() === 0;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Hero Section')
                            ->description('Configure the main header text displayed at the top of the Contact Page.')
                            ->schema([
                                Forms\Components\TextInput::make('hero_badge')
                                    ->label('Hero Badge / Subtitle Label')
                                    ->default('Correspondence')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('hero_title')
                                    ->label('Main Hero Title')
                                    ->default('Write to the atelier.')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('hero_subtitle')
                                    ->label('Hero Subtitle Description')
                                    ->default('Custom orders, trade inquiries, press or simply to say hello. We answer every inquiry within 24 hours.')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Section::make('Workshop & Atelier Information')
                            ->description('Configure workshop location details, opening hours, and contact channels.')
                            ->schema([
                                Forms\Components\TextInput::make('workshop_label')
                                    ->label('Location Card Badge / Title')
                                    ->default('Workshop')
                                    ->helperText('Badge displayed above the physical address (e.g. Workshop, Atelier, Resin Workshop)')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('studio_address')
                                    ->label('Workshop Physical Address')
                                    ->default("14 rue des Étoiles\n33000 Bordeaux, France")
                                    ->rows(3)
                                    ->required(),

                                Forms\Components\Textarea::make('studio_hours')
                                    ->label('Workshop Opening & Visiting Hours')
                                    ->default("By appointment · Tuesday – Saturday\n10h – 18h")
                                    ->rows(3)
                                    ->required(),

                                Forms\Components\TextInput::make('studio_email')
                                    ->label('Contact / Workshop Email')
                                    ->email()
                                    ->default('hello@maisonresine.co')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('studio_phone')
                                    ->label('Contact / Workshop Phone')
                                    ->default('+33 5 56 00 00 00')
                                    ->required()
                                    ->maxLength(255),
                            ])->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hero_title')
                    ->label('Hero Title')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('workshop_label')
                    ->label('Location Badge')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('studio_email')
                    ->label('Workshop Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('studio_phone')
                    ->label('Workshop Phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListContactPageContents::route('/'),
            'create' => Pages\CreateContactPageContent::route('/create'),
            'edit' => Pages\EditContactPageContent::route('/manage'),
        ];
    }
}
