<?php

namespace App\Filament\Widgets;

use App\Models\CustomRequest;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentCustomRequestsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Custom Artwork Inquiries';

    protected static ?int $sort = 6;

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CustomRequest::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('public_reference')
                    ->label('Reference')
                    ->weight('bold')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Client Name')
                    ->description(function (CustomRequest $record): string {
                        $phone = $record->phone ? preg_replace('/[\r\n]+/', ' ', trim($record->phone)) : null;
                        if ($phone && strlen($phone) > 18) {
                            $phone = \Illuminate\Support\Str::limit($phone, 16);
                        }
                        return $record->email . ($phone ? ' · ' . $phone : '');
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('project_type')
                    ->label('Project Type')
                    ->badge()
                    ->color('primary')
                    ->wrap(),

                Tables\Columns\TextColumn::make('dimensions')
                    ->label('Dimensions')
                    ->getStateUsing(fn (CustomRequest $record) => ($record->width && $record->height) ? ($record->width . ' × ' . $record->height . ' ' . ($record->unit ?? 'cm')) : 'Custom'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof \App\Enums\CustomRequestStatus ? $state->value : (string) $state) {
                        'SUBMITTED' => 'warning',
                        'UNDER_REVIEW' => 'info',
                        'QUOTED' => 'primary',
                        'ACCEPTED', 'DEPOSIT_PAID', 'COMPLETED' => 'success',
                        'DECLINED', 'CANCELLED' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since(),
            ])
            ->actions([
                Action::make('review')
                    ->label('Review & Quote')
                    ->icon('heroicon-m-sparkles')
                    ->button()
                    ->size('xs')
                    ->color('warning')
                    ->url(fn (CustomRequest $record): string => '/admin/custom-requests/' . $record->id . '/edit'),
            ])
            ->paginated(false);
    }
}
