<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Filament\Resources\BlogPostResource\RelationManagers;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enums\BlogPostStatus;
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Atelier & Content Pages';

    protected static ?string $navigationLabel = 'Journal Articles';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('General Information')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(BlogPost::class, 'slug', ignoreRecord: true),

                                Forms\Components\Select::make('category_id')
                                    ->label('Category')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\TextInput::make('author_name')
                                    ->label('Author Name')
                                    ->default('Atelier Artisan')
                                    ->maxLength(255),
                            ])->columns(2),

                        Forms\Components\Section::make('Article Content')
                            ->schema([
                                Forms\Components\Textarea::make('excerpt')
                                    ->label('Short Excerpt')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('content')
                                    ->label('Main Article Content')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Media Assets')
                            ->schema([
                                Forms\Components\FileUpload::make('featured_image')
                                    ->label('Featured Hero Image')
                                    ->disk('public')
                                    ->directory('blog')
                                    ->visibility('public')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120)
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('og_image')
                                    ->label('Social Share (Open Graph) Image')
                                    ->disk('public')
                                    ->directory('blog/og')
                                    ->visibility('public')
                                    ->image()
                                    ->maxSize(5120)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status & Publishing')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options(BlogPostStatus::class)
                                    ->default(BlogPostStatus::DRAFT->value)
                                    ->required(),

                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Publish Date')
                                    ->default(now()),

                                Forms\Components\TextInput::make('reading_time')
                                    ->label('Reading Time (Auto-calculated if blank)')
                                    ->placeholder('5 MIN'),
                            ]),

                        Forms\Components\Section::make('SEO Metadata')
                            ->schema([
                                Forms\Components\TextInput::make('seo_title')
                                    ->label('Meta Title')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('seo_description')
                                    ->label('Meta Description')
                                    ->rows(3),
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
                Tables\Columns\ImageColumn::make('featured_image')
                    ->disk('public')
                    ->square()
                    ->label('Thumbnail'),

                Tables\Columns\TextColumn::make('title')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof BlogPostStatus ? $state->value : $state) {
                        'PUBLISHED' => 'success',
                        'DRAFT' => 'gray',
                        'ARCHIVED' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('reading_time')
                    ->fontFamily('mono')
                    ->label('Reading Time'),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->options(BlogPostStatus::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('viewArticle')
                    ->label('View Public')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (BlogPost $record): string => "/blog/{$record->slug}", shouldOpenInNewTab: true)
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
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
