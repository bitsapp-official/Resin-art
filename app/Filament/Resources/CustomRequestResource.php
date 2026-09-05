<?php

namespace App\Filament\Resources;

use App\Enums\CustomRequestStatus;
use App\Filament\Resources\CustomRequestResource\Pages;
use App\Models\CustomRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomRequestResource extends Resource
{
    protected static ?string $model = CustomRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationGroup = 'Orders & Sales';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Customer Inquiries';
    protected static ?string $pluralModelLabel = 'Customer Inquiries';

    /**
     * Minimal 5-step status options for admin dropdown UI
     */
    public static function getStatusOptions(): array
    {
        return [
            CustomRequestStatus::SUBMITTED->value     => '1. New Request',
            CustomRequestStatus::UNDER_REVIEW->value  => '2. Under Review',
            CustomRequestStatus::IN_PRODUCTION->value => '3. In Production',
            CustomRequestStatus::SHIPPED->value       => '4. Dispatched',
            CustomRequestStatus::DELIVERED->value     => '5. Delivered',
            CustomRequestStatus::DECLINED->value      => 'Declined',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORM (View / Edit — Cleaned up to match website /custom form fields)
    // ─────────────────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->disabled(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->disabled(),
                        Forms\Components\TextInput::make('whatsapp')
                            ->label('Phone / WhatsApp')
                            ->disabled(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Delivery Address / Destination')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Requirement Details')
                    ->schema([
                        Forms\Components\Textarea::make('idea_description')
                            ->label('Customer Requirement Description')
                            ->disabled()
                            ->columnSpanFull()
                            ->rows(4),
                    ]),

                Forms\Components\Section::make('Inspiration Photos')
                    ->schema([
                        Forms\Components\Placeholder::make('inspiration_images')
                            ->label('Uploaded Files')
                            ->content(function ($record) {
                                if (!$record || $record->images->isEmpty()) {
                                    return 'No reference photos uploaded by client.';
                                }
                                $html = '<div style="display: flex; gap: 14px; flex-wrap: wrap; margin-top: 8px;">';
                                foreach ($record->images as $img) {
                                    $url = asset('storage/' . $img->file_path);
                                    $html .= '<a href="' . $url . '" target="_blank" style="display: block; border: 2px solid #3F3F46; border-radius: 12px; overflow: hidden; background: #18181B; transition: transform 0.2s;" title="Click to view full image">';
                                    $html .= '<img src="' . $url . '" style="width: 120px; height: 120px; object-fit: cover; display: block;" />';
                                    $html .= '</a>';
                                }
                                $html .= '</div>';
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ]),

                Forms\Components\Section::make('Request Status & Internal Notes')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(static::getStatusOptions())
                            ->required(),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Notes (Admin Only)')
                            ->rows(2)
                            ->placeholder('Add any internal notes about this request...'),
                    ])->columns(2),
            ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TABLE (List View)
    // ─────────────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('public_reference')
                    ->label('Reference')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn ($record) => $record->email),

                Tables\Columns\TextColumn::make('idea_description')
                    ->label('Requirement Description')
                    ->limit(80)
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(static::getStatusOptions()),
            ])
            ->actions([
                // ── Quick: Update Status ──
                Tables\Actions\Action::make('update_status')
                    ->label('Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options(static::getStatusOptions())
                            ->required(),
                    ])
                    ->fillForm(fn ($record) => ['status' => $record->status->value ?? $record->status])
                    ->action(function ($record, array $data) {
                        $record->update(['status' => $data['status']]);
                        Notification::make()
                            ->title('Status updated to: ' . (CustomRequestStatus::tryFrom($data['status'])?->getLabel() ?? $data['status']))
                            ->success()
                            ->send();
                    }),

                // ── Direct WhatsApp Action ──
                Tables\Actions\Action::make('whatsapp_contact')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(function ($record) {
                        $phone = preg_replace('/[^0-9]/', '', $record->whatsapp ?: $record->phone);
                        if (!$phone) return '#';
                        $msg = rawurlencode("Hello {$record->name}, this is Maison Résine Atelier regarding your custom artwork request ({$record->public_reference}).");
                        return "https://wa.me/{$phone}?text={$msg}";
                    })
                    ->openUrlInNewTab(true)
                    ->visible(fn ($record) => !empty($record->whatsapp || $record->phone)),

                // ── Create Receipt / Invoice Action ──
                Tables\Actions\Action::make('create_receipt')
                    ->label('Create Receipt / Invoice')
                    ->icon('heroicon-o-document-currency-dollar')
                    ->color('primary')
                    ->url(fn ($record) => InvoiceResource::getUrl('create') . '?request=' . $record->id)
                    ->openUrlInNewTab(false),

                // ── View Full Details ──
                Tables\Actions\EditAction::make()
                    ->label('View'),
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
            'index' => Pages\ListCustomRequests::route('/'),
            'edit'  => Pages\EditCustomRequest::route('/{record}/edit'),
        ];
    }
}
