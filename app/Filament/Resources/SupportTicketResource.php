<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Customers & Support';

    protected static ?string $navigationLabel = 'Support Tickets';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ticket Overview')
                    ->schema([
                        Forms\Components\TextInput::make('ticket_number')->disabled(),
                        Forms\Components\TextInput::make('subject')->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'OPEN' => 'Open',
                                'IN_PROGRESS' => 'In Progress',
                                'WAITING_FOR_CUSTOMER' => 'Waiting for Customer',
                                'RESOLVED' => 'Resolved',
                                'CLOSED' => 'Closed',
                            ])
                            ->required(),
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->fontFamily('mono')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->searchable(),
                Tables\Columns\TextColumn::make('subject')->searchable(),
                Tables\Columns\TextColumn::make('category'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'OPEN' => 'danger',
                        'IN_PROGRESS', 'WAITING_FOR_CUSTOMER' => 'warning',
                        'RESOLVED' => 'success',
                        'CLOSED' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M d, Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportTickets::route('/'),
            'edit' => Pages\EditSupportTicket::route('/{record}/edit'),
        ];
    }
}
