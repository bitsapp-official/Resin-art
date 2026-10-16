<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
