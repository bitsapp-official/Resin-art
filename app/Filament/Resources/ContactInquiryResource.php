<?php

namespace App\Filament\Resources;

use App\Enums\ContactInquiryStatus;
use App\Filament\Resources\ContactInquiryResource\Pages;
use App\Mail\ContactInquiryReply;
use App\Models\ContactInquiry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class ContactInquiryResource extends Resource
{
    protected static ?string $model = ContactInquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Customers & Support';

    protected static ?string $navigationLabel = 'Contact Inquiries';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ─── Left: Customer Message ───────────────────────────────
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Customer Details')
                            ->schema([
                                Forms\Components\TextInput::make('public_reference')
                                    ->label('Reference')
                                    ->disabled(),

                                Forms\Components\TextInput::make('name')
                                    ->label('Customer Name')
                                    ->disabled(),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email Address')
                                    ->disabled(),

                                Forms\Components\TextInput::make('phone')
                                    ->label('Phone Number')
                                    ->disabled(),
                            ])->columns(2),

                        Forms\Components\Section::make('Message')
                            ->schema([
                                Forms\Components\TextInput::make('subject')
                                    ->label('Subject')
                                    ->disabled()
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('message')
                                    ->label('Message')
                                    ->rows(8)
                                    ->disabled()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                // ─── Right: Status Management ─────────────────────────────
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Inquiry Status')
                                    ->options(array_combine(
                                        array_column(ContactInquiryStatus::cases(), 'value'),
                                        array_map(fn ($case) => $case->label(), ContactInquiryStatus::cases())
                                    ))
                                    ->required(),
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
                Tables\Columns\TextColumn::make('public_reference')
                    ->label('Reference')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Customer')
                    ->searchable(['name', 'email'])
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->email),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->limit(35),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ContactInquiryStatus ? $state->label() : ContactInquiryStatus::tryFrom($state)?->label() ?? $state)
                    ->color(fn ($state) => match (is_string($state) ? ContactInquiryStatus::tryFrom($state)?->color() : $state?->color()) {
                        'danger'  => 'danger',
                        'info'    => 'info',
                        'warning' => 'warning',
                        'success' => 'success',
                        default   => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(array_combine(
                        array_column(ContactInquiryStatus::cases(), 'value'),
                        array_map(fn ($case) => $case->label(), ContactInquiryStatus::cases())
                    )),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // ─── Reply Customer via Email ──────────────────────────────
                Tables\Actions\Action::make('sendReply')
                    ->label('Reply')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('reply_message')
                            ->label('Your Reply')
                            ->placeholder('Type your reply to the customer here...')
                            ->rows(6)
                            ->required(),
                    ])
                    ->action(function (ContactInquiry $record, array $data) {
                        try {
                            Mail::to($record->email)->queue(new ContactInquiryReply($record, $data['reply_message']));

                            $record->update([
                                'status'     => ContactInquiryStatus::REPLIED,
                                'replied_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Reply sent to ' . $record->name)
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Failed to send reply: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // ─── Close Inquiry ─────────────────────────────────────────
                Tables\Actions\Action::make('closeInquiry')
                    ->label('Close')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->visible(fn (ContactInquiry $record) => $record->status !== ContactInquiryStatus::CLOSED)
                    ->requiresConfirmation()
                    ->action(function (ContactInquiry $record) {
                        $record->update([
                            'status'    => ContactInquiryStatus::CLOSED,
                            'closed_at' => now(),
                        ]);
                        Notification::make()->title('Inquiry closed')->success()->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactInquiries::route('/'),
            'view' => Pages\ViewContactInquiry::route('/{record}'),
            'edit' => Pages\ViewContactInquiry::route('/{record}/edit'),
        ];
    }
}
