<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PolicyPageResource\Pages;
use App\Models\PolicyPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PolicyPageResource extends Resource
{
    protected static ?string $model = PolicyPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Atelier & Content Pages';

    protected static ?string $navigationLabel = 'Legal & Policy Pages';

    protected static ?string $modelLabel = 'Policy Page';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Page Identity')
                ->description('These define the page URL and title shown to visitors.')
                ->schema([
                    Forms\Components\TextInput::make('slug')
                        ->label('Page Slug (URL identifier)')
                        ->required()
                        ->disabled(fn ($record) => $record !== null)
                        ->unique(ignoreRecord: true)
                        ->placeholder('e.g. shipping, return, privacy, terms')
                        ->helperText('Cannot be changed after creation. Use: shipping, return, privacy, or terms'),

                    Forms\Components\TextInput::make('title')
                        ->label('Page Title')
                        ->required()
                        ->placeholder('e.g. Shipping Policy'),
                ])->columns(2),

            Forms\Components\Section::make('Page Hero (Top Banner)')
                ->description('Appears at the top of the page above the content.')
                ->schema([
                    Forms\Components\TextInput::make('hero_badge')
                        ->label('Hero Badge Label (Small Caps)')
                        ->placeholder('e.g. DISPATCH & DELIVERY')
                        ->helperText('Short all-caps label above the title'),

                    Forms\Components\TextInput::make('hero_label')
                        ->label('Hero Subtitle Line')
                        ->placeholder('e.g. How we pack & ship your piece.'),
                ])->columns(2),

            Forms\Components\Section::make('Policy Content')
                ->description('Full page body content. Supports rich text formatting, headings, links, and lists.')
                ->schema([
                    Forms\Components\RichEditor::make('content')
                        ->label('Page Content')
                        ->toolbarButtons([
                            'heading',
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'link',
                            'bulletList',
                            'orderedList',
                            'redo',
                            'undo',
                        ])
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('SEO (Search Engine Optimisation)')
                ->schema([
                    Forms\Components\TextInput::make('meta_title')
                        ->label('Meta Title')
                        ->placeholder('e.g. Shipping Policy — Maison Résine')
                        ->maxLength(70),

                    Forms\Components\Textarea::make('meta_description')
                        ->label('Meta Description')
                        ->placeholder('Brief summary for Google search snippets (max 160 chars)')
                        ->maxLength(160)
                        ->rows(2),
                ])->columns(2)->collapsible()->collapsed(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'shipping' => 'success',
                        'return'   => 'warning',
                        'privacy'  => 'info',
                        'terms'    => 'gray',
                        default    => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->weight('semibold')
                    ->searchable()
                    ->description(fn ($record) => $record->hero_label),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('slug')
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPolicyPages::route('/'),
            'create' => Pages\CreatePolicyPage::route('/create'),
            'edit'   => Pages\EditPolicyPage::route('/{record}/edit'),
        ];
    }
}
