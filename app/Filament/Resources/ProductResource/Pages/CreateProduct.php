<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->success()
            ->title('Product created successfully');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $canonicalPurities = ['10KT Gold', '14KT Gold', '18KT Gold', '22KT Gold', '925 Silver'];
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
            $profit = (float) ($data['attributes']['profit_margin_percent'] ?? 0);
            $cutPercent = max(0.0, (float) ($data['attributes']['cut_price_percent'] ?? 0));
            if ($cutPercent > 0) {
                $data['attributes']['common_cut_price'] = null;
            }
            $commonCut = (float) ($data['attributes']['common_cut_price'] ?? 0);

            $caratVal = $data['attributes']['diamond_carat'] ?? 0;
            $caratNum = 0.0;
            if (is_numeric($caratVal) && (float) $caratVal > 0) {
                $caratNum = (float) $caratVal;
            } elseif (!empty($caratVal) && preg_match('/([\d\.]+)/', (string) $caratVal, $m)) {
                $caratNum = (float) $m[1];
            } elseif (!empty($data['attributes']['diamond_carat_options']) && is_array($data['attributes']['diamond_carat_options'])) {
                $first = reset($data['attributes']['diamond_carat_options']);
                if (preg_match('/([\d\.]+)/', (string) $first, $m)) {
                    $caratNum = (float) $m[1];
                    $data['attributes']['diamond_carat'] = $caratNum;
                }
            }

            $clarities = (array) ($data['attributes']['diamond_clarities'] ?? ['VVS1', 'VVS2', 'VS1', 'VS2']);
            $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';

            if ($caratNum > 0) {
                $diamond = round($caratNum * \App\Services\BullionPricingService::getDiamondRateForClarity($firstClarity, $rates), 2);
                $data['attributes']['diamond_value'] = $diamond;
            } else {
                $diamond = 0.0;
                $data['attributes']['diamond_value'] = 0.0;
            }

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
                        $profit,
                        $caratNum > 0 ? $caratNum : null,
                        $firstClarity
                    );
                    $data['attributes']['purity_prices'][$p] = $calc['final_price'];
                    $data['attributes']['purity_mrp'][$p] = \App\Services\BullionPricingService::calculateStrikeMrp($calc['final_price'], $data['attributes'], $calc['mrp_price']);
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
            $fallbackMrp = isset($purityMrp[$firstPurity]) && is_numeric($purityMrp[$firstPurity]) ? (float) $purityMrp[$firstPurity] : null;
            $mrp = $sellingPrice !== null
                ? \App\Services\BullionPricingService::calculateStrikeMrp($sellingPrice, $data['attributes'], $fallbackMrp)
                : ($commonCut > 0 ? $commonCut : $fallbackMrp);

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
