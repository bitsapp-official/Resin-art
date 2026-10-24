<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_product')
                ->label('View on Website')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('primary')
                ->url(fn (): ?string => !empty($this->record->slug) ? route('shop.show', $this->record->slug) : null)
                ->openUrlInNewTab()
                ->visible(fn (): bool => !empty($this->record->slug)),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        $notification = \Filament\Notifications\Notification::make()
            ->success()
            ->title('Product saved successfully');

        if (!empty($this->record->slug)) {
            $notification->actions([
                \Filament\Notifications\Actions\Action::make('view_site')
                    ->label('View on Website')
                    ->button()
                    ->url(route('shop.show', $this->record->slug), shouldOpenInNewTab: true),
            ]);
        }

        return $notification;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $canonicalPurities = ['14KT Gold', '18KT Gold', '22KT Gold'];
        $purities = array_values(array_filter((array) ($data['attributes']['gold_purities'] ?? [])));

        if (!empty($purities)) {
            usort($purities, function ($a, $b) use ($canonicalPurities) {
                $posA = array_search($a, $canonicalPurities);
                $posB = array_search($b, $canonicalPurities);
                $idxA = $posA !== false ? $posA : 999;
                $idxB = $posB !== false ? $posB : 999;
                return $idxA <=> $idxB;
            });
            $data['attributes']['gold_purities'] = $purities;
            $data['attributes']['pricing_mode'] = 'dynamic';
        } else {
            $data['attributes']['gold_purities'] = [];
            $data['attributes']['pricing_mode'] = 'manual';
        }

        // If Automatic Gold Formula is active
        if ($data['attributes']['pricing_mode'] === 'dynamic') {
            $rates = \App\Services\BullionPricingService::getRates();
            $making = (float) ($data['attributes']['making_charge'] ?? $rates['making_per_gram']);
            if ($making <= 0) {
                $making = (float) ($rates['making_per_gram'] ?: 750);
            }
            $diamond = (float) ($data['attributes']['diamond_value'] ?? 0);
            $profit = (float) ($data['attributes']['profit_margin_percent'] ?? 0);
            $commonCut = (float) ($data['attributes']['common_cut_price'] ?? 0);

            // Clean size variants: keep only size labels, price and weight are fixed from benchmark
            if (($data['attributes']['has_sizes'] ?? 'yes') === 'no') {
                $data['attributes']['size_variants'] = [];
            } else {
                $cleanVariants = [];
                foreach ((array) ($data['attributes']['size_variants'] ?? []) as $v) {
                    $sName = trim($v['size'] ?? '');
                    if (!empty($sName)) {
                        $cleanVariants[] = ['size' => $sName];
                    }
                }
                $data['attributes']['size_variants'] = $cleanVariants;
            }

            $weight = (float) ($data['attributes']['gold_weight_grams'] ?? 0);
            if ($weight > 0) {
                foreach ($purities as $p) {
                    $calc = \App\Services\BullionPricingService::calculateFinalPrice(
                        $weight,
                        $making,
                        $diamond,
                        $p,
                        null,
                        null,
                        $weight,
                        $profit
                    );
                    $data['attributes']['purity_prices'][$p] = $calc['final_price'];
                    $data['attributes']['purity_mrp'][$p] = ($commonCut > 0) ? $commonCut : $calc['mrp_price'];
                }

                $data['attributes']['gold_weight'] = "{$weight} gms approx";
                $data['attributes']['gold_rates_synced_at'] = now()->toDateTimeString();
            }

            $firstPurity = $purities[0];
            $purityPrices = $data['attributes']['purity_prices'] ?? [];
            $purityMrp = $data['attributes']['purity_mrp'] ?? [];

            $sellingPrice = isset($purityPrices[$firstPurity]) && is_numeric($purityPrices[$firstPurity])
                ? (float) $purityPrices[$firstPurity]
                : null;
            $mrp = ($commonCut > 0)
                ? $commonCut
                : (isset($purityMrp[$firstPurity]) && is_numeric($purityMrp[$firstPurity]) ? (float) $purityMrp[$firstPurity] : null);

            if ($sellingPrice !== null) {
                if ($mrp !== null && $mrp > $sellingPrice) {
                    $data['price'] = $mrp;
                    $data['sale_price'] = $sellingPrice;
                } else {
                    $data['price'] = $sellingPrice;
                    $data['sale_price'] = null;
                }
            }
        }

        $canonicalColors = ['Yellow Gold', 'White Gold', 'Rose Gold'];
        $colors = array_values(array_filter((array) ($data['attributes']['metal_colors'] ?? [])));
        if (!empty($colors)) {
            usort($colors, function ($a, $b) use ($canonicalColors) {
                $posA = array_search($a, $canonicalColors);
                $posB = array_search($b, $canonicalColors);
                $idxA = $posA !== false ? $posA : 999;
                $idxB = $posB !== false ? $posB : 999;
                return $idxA <=> $idxB;
            });
            $data['attributes']['metal_colors'] = $colors;
        }

        return $data;
    }
}
