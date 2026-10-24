<?php

namespace App\Filament\Resources\ContactInquiryResource\Pages;

use App\Filament\Resources\ContactInquiryResource;
use Filament\Resources\Pages\ListRecords;

class ListContactInquiries extends ListRecords
{
    protected static string $resource = ContactInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make('All Inquiries')
                ->badge(\App\Models\ContactInquiry::count()),

            'new' => \Filament\Resources\Components\Tab::make('Unread / New')
                ->modifyQueryUsing(fn ($query) => $query->where('status', \App\Enums\ContactInquiryStatus::NEW))
                ->badge(\App\Models\ContactInquiry::where('status', \App\Enums\ContactInquiryStatus::NEW)->count())
                ->badgeColor('danger'),

            'read' => \Filament\Resources\Components\Tab::make('Read / In Progress')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', [
                    \App\Enums\ContactInquiryStatus::READ,
                    \App\Enums\ContactInquiryStatus::IN_PROGRESS,
                ]))
                ->badge(\App\Models\ContactInquiry::whereIn('status', [
                    \App\Enums\ContactInquiryStatus::READ,
                    \App\Enums\ContactInquiryStatus::IN_PROGRESS,
                ])->count())
                ->badgeColor('warning'),

            'replied' => \Filament\Resources\Components\Tab::make('Replied & Resolved')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', [
                    \App\Enums\ContactInquiryStatus::REPLIED,
                    \App\Enums\ContactInquiryStatus::RESOLVED,
                    \App\Enums\ContactInquiryStatus::CLOSED,
                ]))
                ->badge(\App\Models\ContactInquiry::whereIn('status', [
                    \App\Enums\ContactInquiryStatus::REPLIED,
                    \App\Enums\ContactInquiryStatus::RESOLVED,
                    \App\Enums\ContactInquiryStatus::CLOSED,
                ])->count())
                ->badgeColor('success'),
        ];
    }
}
