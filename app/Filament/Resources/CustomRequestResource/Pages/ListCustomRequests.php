<?php

namespace App\Filament\Resources\CustomRequestResource\Pages;

use App\Filament\Resources\CustomRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListCustomRequests extends ListRecords
{
    protected static string $resource = CustomRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make('All Inquiries')
                ->badge(\App\Models\CustomRequest::count()),

            'new' => \Filament\Resources\Components\Tab::make('New / Unreviewed')
                ->modifyQueryUsing(fn ($query) => $query->where('status', \App\Enums\CustomRequestStatus::SUBMITTED))
                ->badge(\App\Models\CustomRequest::where('status', \App\Enums\CustomRequestStatus::SUBMITTED)->count())
                ->badgeColor('warning'),

            'under_review' => \Filament\Resources\Components\Tab::make('Under Review')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', [
                    \App\Enums\CustomRequestStatus::UNDER_REVIEW,
                    \App\Enums\CustomRequestStatus::NEEDS_INFORMATION,
                    \App\Enums\CustomRequestStatus::QUOTE_PREPARATION,
                    \App\Enums\CustomRequestStatus::QUOTE_SENT,
                ]))
                ->badge(\App\Models\CustomRequest::whereIn('status', [
                    \App\Enums\CustomRequestStatus::UNDER_REVIEW,
                    \App\Enums\CustomRequestStatus::NEEDS_INFORMATION,
                    \App\Enums\CustomRequestStatus::QUOTE_PREPARATION,
                    \App\Enums\CustomRequestStatus::QUOTE_SENT,
                ])->count())
                ->badgeColor('info'),

            'in_production' => \Filament\Resources\Components\Tab::make('In Production')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', [
                    \App\Enums\CustomRequestStatus::CONFIRMED,
                    \App\Enums\CustomRequestStatus::IN_PRODUCTION,
                    \App\Enums\CustomRequestStatus::QUALITY_CHECK,
                ]))
                ->badge(\App\Models\CustomRequest::whereIn('status', [
                    \App\Enums\CustomRequestStatus::CONFIRMED,
                    \App\Enums\CustomRequestStatus::IN_PRODUCTION,
                    \App\Enums\CustomRequestStatus::QUALITY_CHECK,
                ])->count())
                ->badgeColor('primary'),

            'completed' => \Filament\Resources\Components\Tab::make('Dispatched & Delivered')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', [
                    \App\Enums\CustomRequestStatus::READY_TO_SHIP,
                    \App\Enums\CustomRequestStatus::SHIPPED,
                    \App\Enums\CustomRequestStatus::DELIVERED,
                ]))
                ->badge(\App\Models\CustomRequest::whereIn('status', [
                    \App\Enums\CustomRequestStatus::READY_TO_SHIP,
                    \App\Enums\CustomRequestStatus::SHIPPED,
                    \App\Enums\CustomRequestStatus::DELIVERED,
                ])->count())
                ->badgeColor('success'),
        ];
    }
}
