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
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
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

            // 1. Calculate each size variant price and auto-derive benchmark weight
            if (!empty($data['attributes']['size_variants'])) {
                $derivedWeight = 0;

                foreach ($data['attributes']['size_variants'] as $idx => $v) {
                    $vWeight = (float) ($v['weight_grams'] ?? 0);
                    $userMrp14k = !empty($v['mrp_14k']) ? (float) $v['mrp_14k'] : null;
                    $userMrp18k = !empty($v['mrp_18k']) ? (float) $v['mrp_18k'] : null;
                    $userMrp22k = !empty($v['mrp_22k']) ? (float) $v['mrp_22k'] : null;

                    if ($vWeight > 0) {
                        $c14 = \App\Services\BullionPricingService::calculateFinalPrice($vWeight, $making, $diamond, '14KT Gold', null, null, $vWeight, $profit);
                        $c18 = \App\Services\BullionPricingService::calculateFinalPrice($vWeight, $making, $diamond, '18KT Gold', null, null, $vWeight, $profit);
                        $c22 = \App\Services\BullionPricingService::calculateFinalPrice($vWeight, $making, $diamond, '22KT Gold', null, null, $vWeight, $profit);

                        $data['attributes']['size_variants'][$idx]['price_14k'] = $c14['final_price'];
                        $data['attributes']['size_variants'][$idx]['mrp_14k']   = $userMrp14k;

                        $data['attributes']['size_variants'][$idx]['price_18k'] = $c18['final_price'];
                        $data['attributes']['size_variants'][$idx]['mrp_18k']   = $userMrp18k;

                        $data['attributes']['size_variants'][$idx]['price_22k'] = $c22['final_price'];
                        $data['attributes']['size_variants'][$idx]['mrp_22k']   = $userMrp22k;

                        $data['attributes']['size_variants'][$idx]['price']     = $c18['final_price'];
                        $data['attributes']['size_variants'][$idx]['mrp']       = $userMrp18k;

                        if (($v['size'] ?? '') === 'Size 12' || $derivedWeight <= 0) {
                            $derivedWeight = $vWeight;
                        }
                    }
                }

                if ($derivedWeight > 0) {
                    $data['attributes']['gold_weight_grams'] = $derivedWeight;
                }
            }

            $weight = (float) ($data['attributes']['gold_weight_grams'] ?? 0);
            if ($weight > 0) {
                foreach ($purities as $p) {
                    $calc = \App\Services\BullionPricingService::calculatePurityPrice(
                        $p,
                        $weight,
                        $making,
                        'per_gram',
                        $diamond,
                        $rates
                    );
                    $data['attributes']['purity_prices'][$p] = $calc['selling_price'];
                    $data['attributes']['purity_mrp'][$p] = $calc['mrp_price'];
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
            $mrp = isset($purityMrp[$firstPurity]) && is_numeric($purityMrp[$firstPurity])
                ? (float) $purityMrp[$firstPurity]
                : null;

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
