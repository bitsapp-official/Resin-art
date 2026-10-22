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
                                    ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                                        $set('slug', Str::slug($state))
                                    ),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Auto-generated from post title (can also be edited manually).')
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

                                Forms\Components\Toggle::make('is_featured_on_home')
                                    ->label('Showcase on Homepage (Max 3)')
                                    ->helperText('Display this article in the 3-card Editorial Journal on the Homepage (Max 3 allowed)')
                                    ->default(false)
                                    ->rule(function ($record) {
                                        return function (string $attribute, $value, \Closure $fail) use ($record) {
                                            if ($value) {
                                                $count = \App\Models\BlogPost::where('is_featured_on_home', true)
                                                    ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                                    ->count();

                                                if ($count >= 3) {
                                                    $fail('Homepage has slots for exactly 3 showcase articles. Please turn OFF an existing showcase article before activating this one.');
                                                }
                                            }
                                        };
                                    }),
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

                Tables\Columns\ToggleColumn::make('is_featured_on_home')
                    ->label('Home Showcase (Max 3)')
                    ->updateStateUsing(function ($record, $state) {
                        if ($state) {
                            $featuredCount = \App\Models\BlogPost::where('is_featured_on_home', true)
                                ->where('id', '!=', $record->id)
                                ->count();

                            if ($featuredCount >= 3) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Limit Reached: Max 3 Allowed')
                                    ->body('Homepage has slots for exactly 3 showcase articles. Please turn OFF another article before activating this one.')
                                    ->warning()
                                    ->send();

                                return $record->is_featured_on_home;
                            }
                        }

                        $record->update(['is_featured_on_home' => (bool) $state]);

                        \Filament\Notifications\Notification::make()
                            ->title($state ? 'Article Added to Homepage Showcase' : 'Article Removed from Homepage Showcase')
                            ->success()
                            ->send();

                        return $state;
                    }),

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
