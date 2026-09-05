<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Atelier Executive Overview';

    public function getSubheading(): ?string
    {
        return 'Real-time sales analytics, live order processing, bespoke inquiries, and store performance.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reports')
                ->label('Export Reports (Excel/CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url('/admin/store-reports'),

            Action::make('store')
                ->label('Live Storefront')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url('/')
                ->openUrlInNewTab(),
        ];
    }
}
