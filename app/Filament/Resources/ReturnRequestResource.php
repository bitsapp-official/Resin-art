<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnRequestResource\Pages;
use App\Models\ReturnRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReturnRequestResource extends Resource
{
    protected static ?string $model = ReturnRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Customers & Support';

    protected static ?string $navigationLabel = 'Return Requests';

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'REQUESTED' => 'Requested',
                        'UNDER_REVIEW' => 'Under Review',
                        'APPROVED' => 'Approved',
                        'REJECTED' => 'Rejected',
                        'PICKUP_PENDING' => 'Pickup Pending',
                        'RECEIVED' => 'Received',
                        'RESOLVED' => 'Resolved',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('admin_notes')
                    ->label('Internal Admin Notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_reference')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->searchable(),
                Tables\Columns\TextColumn::make('reason'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'APPROVED', 'RESOLVED' => 'success',
                        'REJECTED' => 'danger',
                        'UNDER_REVIEW', 'PICKUP_PENDING' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M d, Y'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnRequests::route('/'),
            'edit' => Pages\EditReturnRequest::route('/{record}/edit'),
        ];
    }
}
