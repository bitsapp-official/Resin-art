<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteSettingResource\Pages;
use App\Filament\Resources\SiteSettingResource\RelationManagers;
use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Store Settings';

    protected static ?string $navigationLabel = 'Footer & Social Settings';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Setting Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->disabled(fn ($record) => $record !== null)
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('group')
                            ->options([
                                'footer' => 'Footer Links & Socials',
                                'general' => 'General Atelier Info',
                                'legal' => 'Legal Pages',
                            ])
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->options([
                                'text' => 'Short Text',
                                'textarea' => 'Multi-line Text',
                                'url' => 'URL / Link',
                                'richtext' => 'Rich Text Document',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\Textarea::make('value')
                            ->label('Setting Value (Text)')
                            ->rows(4)
                            ->visible(fn (Forms\Get $get) => $get('type') !== 'richtext')
                            ->columnSpanFull(),
                            
                        Forms\Components\RichEditor::make('value')
                            ->label('Document Content')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'richtext')
                            ->columnSpanFull()
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->where('key', 'not like', 'global_badge_%')
                ->where('key', 'not like', 'product_tab_%')
                ->where('key', 'not like', 'invoice_%')
                ->where('key', 'not like', 'home_%')
                ->where('key', 'not like', 'contact_%')
            )
            ->columns([
                Tables\Columns\TextColumn::make('group')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'footer' => 'warning',
                        'legal' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->fontFamily('mono')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('value')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('group', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'footer' => 'Footer Links & Socials',
                        'general' => 'General Atelier Info',
                        'legal' => 'Legal Pages',
                    ]),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiteSettings::route('/'),
            'create' => Pages\CreateSiteSetting::route('/create'),
            'edit' => Pages\EditSiteSetting::route('/{record}/edit'),
        ];
    }
}
