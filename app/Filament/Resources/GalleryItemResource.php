<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryItemResource\Pages;
use App\Models\GalleryItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class GalleryItemResource extends Resource
{
    protected static ?string $model = GalleryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-camera';

    protected static ?string $navigationGroup = 'Atelier & Content Pages';

    protected static ?string $navigationLabel = 'Gallery Items';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Item Details')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Title')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Select::make('gallery_category_id')
                                    ->label('Category')
                                    ->relationship('galleryCategory', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\TextInput::make('location')
                                    ->label('Location (Optional)')
                                    ->placeholder('e.g. Bordeaux, France')
                                    ->maxLength(255),
                            ])->columns(3),

                        Forms\Components\Section::make('Media Format & Content')
                            ->description('Choose whether this gallery entry is a high-resolution photograph or a YouTube video showcase.')
                            ->schema([
                                Forms\Components\ToggleButtons::make('media_type')
                                    ->label('Select Media Type')
                                    ->options([
                                        'image' => 'Photography (Photo)',
                                        'video' => 'YouTube Video',
                                    ])
                                    ->icons([
                                        'image' => 'heroicon-o-camera',
                                        'video' => 'heroicon-o-play-circle',
                                    ])
                                    ->colors([
                                        'image' => 'primary',
                                        'video' => 'warning',
                                    ])
                                    ->default('image')
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record) {
                                            $component->state($record->media_type ?? (!empty($record->video_url) ? 'video' : 'image'));
                                        }
                                    })
                                    ->inline()
                                    ->live()
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('video_url')
                                    ->label('YouTube Video URL')
                                    ->placeholder('https://www.youtube.com/watch?v=... or https://youtu.be/... or Shorts')
                                    ->url()
                                    ->required(fn (Forms\Get $get) => $get('media_type') === 'video')
                                    ->rules([
                                        fn () => function (string $attribute, $value, \Closure $fail) {
                                            if (!empty($value) && !preg_match('/(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:embed\/|v\/|shorts\/|watch\?(?:.*&)?v=))([\w-]{11})/i', $value)) {
                                                $fail('Please enter a valid YouTube video link (watch, shorts, or youtu.be). Only YouTube links are supported.');
                                            }
                                        },
                                    ])
                                    ->helperText('Only valid YouTube links (Watch, Shorts, or youtu.be) are accepted. Video player and thumbnail poster are handled automatically.')
                                    ->visible(fn (Forms\Get $get) => $get('media_type') === 'video')
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Upload Photography Image')
                                    ->disk('public')
                                    ->directory('gallery')
                                    ->visibility('public')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                                    ->maxSize(5120)
                                    ->required(fn (Forms\Get $get) => ($get('media_type') ?? 'image') === 'image')
                                    ->visible(fn (Forms\Get $get) => ($get('media_type') ?? 'image') === 'image')
                                    ->helperText('Upload high-resolution photography image (JPG, PNG, WebP, AVIF — Max 5 MB).')
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('image_alt')
                                    ->label('Accessibility Alt Text')
                                    ->placeholder('e.g. Handcrafted Ocean Wave Resin Artwork')
                                    ->nullable()
                                    ->default(fn ($get) => $get('title') ?? 'Resin art gallery piece')
                                    ->helperText('Descriptive alt text for accessibility and search engines.')
                                    ->visible(fn (Forms\Get $get) => ($get('media_type') ?? 'image') === 'image')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Publishing & Visibility')
                            ->schema([
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured Item')
                                    ->default(false),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active / Published')
                                    ->default(true),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Thumbnail')
                    ->disk('public')
                    ->square()
                    ->size(60)
                    ->defaultImageUrl(fn ($record) => $record->thumbnail_url),

                Tables\Columns\IconColumn::make('is_video')
                    ->label('Type')
                    ->boolean()
                    ->trueIcon('heroicon-o-video-camera')
                    ->falseIcon('heroicon-o-photo')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->is_video ? 'YouTube Video' : 'Photography Photo'),

                Tables\Columns\TextColumn::make('title')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('galleryCategory.name')
                    ->label('Category')
                    ->badge()
                    ->color('amber')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('gallery_category_id')
                    ->label('Category')
                    ->relationship('galleryCategory', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),

                    Tables\Actions\BulkAction::make('mark_featured')
                        ->label('Mark as Featured')
                        ->icon('heroicon-o-star')
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => true])),

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
            'index' => Pages\ListGalleryItems::route('/'),
            'create' => Pages\CreateGalleryItem::route('/create'),
            'edit' => Pages\EditGalleryItem::route('/{record}/edit'),
        ];
    }
}
