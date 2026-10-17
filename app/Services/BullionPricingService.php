<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Log;

class BullionPricingService
{
    /**
     * Standard gold purities handled in fine jewellery.
     */
    public const PURITIES = ['14KT Gold', '18KT Gold', '22KT Gold'];

    /**
     * Get current live bullion rates per gram from SiteSettings.
     *
     * @return array{
     *     rate_24kt: float,
     *     rate_22kt: float,
     *     rate_18kt: float,
     *     rate_14kt: float,
     *     making_per_gram: float,
     *     gst_rate: float,
     *     updated_at: string
     * }
     */
    public static function getRates(): array
    {
        $rate24kt = (float) SiteSetting::get('gold_rate_24kt', 7600);
        $rate22kt = (float) SiteSetting::get('gold_rate_22kt', round($rate24kt * (22 / 24), 2));
        $rate18kt = (float) SiteSetting::get('gold_rate_18kt', round($rate24kt * (18 / 24), 2));
        $rate14kt = (float) SiteSetting::get('gold_rate_14kt', round($rate24kt * (14 / 24), 2));
        $makingPerGram = (float) SiteSetting::get('gold_default_making_per_gram', 650);
        $gstRate = (float) SiteSetting::get('gold_gst_rate', 3.0);
        $mrpMarkupPercent = (float) SiteSetting::get('gold_mrp_markup_percent', 20.0);
        $updatedAt = (string) SiteSetting::get('gold_rates_updated_at', now()->toDateTimeString());

        return [
            'rate_24kt'           => $rate24kt,
            'rate_22kt'           => $rate22kt,
            'rate_18kt'           => $rate18kt,
            'rate_14kt'           => $rate14kt,
            'making_per_gram'     => $makingPerGram,
            'gst_rate'            => $gstRate,
            'mrp_markup_percent'  => $mrpMarkupPercent,
            'updated_at'          => $updatedAt,
        ];
    }

    /**
     * Return rate per gram for a specific Karat.
     */
    public static function getRateForPurity(string $purity, ?array $rates = null): float
    {
        $rates = $rates ?? self::getRates();

        return match ($purity) {
            '22KT Gold', '22KT', '22k', '22K' => $rates['rate_22kt'],
            '18KT Gold', '18KT', '18k', '18K' => $rates['rate_18kt'],
            '14KT Gold', '14KT', '14k', '14K' => $rates['rate_14kt'],
            '24KT Gold', '24KT', '24k', '24K' => $rates['rate_24kt'],
            default => $rates['rate_18kt'],
        };
    }

    /**
     * Compute exact metallurgical density multiplier:
     * 14K / 14KT = 1.000 (Base density anchor)
     * 18K / 18KT = 1.185 (+18.5% weight due to metal density)
     * 22K / 22KT = 1.365 (+36.5% weight due to metal density)
     */
    public static function getDensityMultiplier(?string $purity): float
    {
        if (empty($purity)) {
            return 1.185;
        }

        $p = strtolower(trim($purity));
        if (str_contains($p, '14k')) {
            return 1.000;
        }
        if (str_contains($p, '22k')) {
            return 1.365;
        }
        return 1.185;
    }

    /**
     * Compute Ring Size scaling multiplier:
     * Formula: 1 + (($size - 12) * 0.015) [Increases/decreases weight by 1.5% per size step from Size 12].
     */
    public static function getSizeMultiplier(?string $size): float
    {
        if (empty($size)) {
            return 1.000;
        }

        if (preg_match('/([\d\.]+)/', $size, $matches)) {
            $numericSize = (float) $matches[1];
            if ($numericSize >= 5 && $numericSize <= 25) {
                return 1.0 + (($numericSize - 12.0) * 0.015);
            }
        }

        return 1.000;
    }

    /**
     * Calculate Karat Gold Rate from 24K Live Rate:
     * Karat Gold Rate = 24K Live Rate * (Karat / 24)
     */
    public static function getKaratRate(string $purity, ?float $rate24kt = null): float
    {
        if ($rate24kt === null || $rate24kt <= 0) {
            $rates = self::getRates();
            $rate24kt = $rates['rate_24kt'];
        }

        $p = strtolower(trim($purity));
        if (str_contains($p, '14k')) {
            return round($rate24kt * (14.0 / 24.0), 2);
        }
        if (str_contains($p, '22k')) {
            return round($rate24kt * (22.0 / 24.0), 2);
        }
        // Default to 18K
        return round($rate24kt * (18.0 / 24.0), 2);
    }

    /**
     * Dynamic Pricing Matrix Valuation Engine:
     *
     * 1. Karat Gold Rate = 24K Live Rate * (Karat / 24)
     * 2. Final Gold Weight = base_gold_weight * Density Multiplier * Size Multiplier
     * 3. Gold Value = Final Gold Weight * Karat Gold Rate
     * 4. Making Value = Final Gold Weight * making_charge_per_gram
     * 5. Subtotal = Gold Value + Making Value + diamond_stone_value
     * 6. Final Price = Round(Subtotal * 1.03) [Includes 3% Indian GST compliance].
     *
     * @param float $baseGoldWeight (at benchmark Size 12 in 14KT)
     * @param float $makingChargePerGram
     * @param float $diamondStoneValue
     * @param string|null $purity
     * @param string|null $size
     * @param float|null $live24kRate
     * @return array
     */
    public static function calculateFinalPrice(
        float $baseGoldWeight,
        float $makingChargePerGram = 0.0,
        float $diamondStoneValue = 0.0,
        ?string $purity = '18KT Gold',
        ?string $size = 'Size 12',
        ?float $live24kRate = null,
        ?float $explicitWeightGrams = null
    ): array {
        $rates = self::getRates();
        $rate24kt = ($live24kRate !== null && $live24kRate > 0) ? $live24kRate : $rates['rate_24kt'];

        // Use exact weight for the selected size if provided, otherwise use base gold weight.
        // No artificial mathematical scaling: the jeweler's exact physical weight is respected.
        $finalGoldWeight = ($explicitWeightGrams !== null && $explicitWeightGrams > 0)
            ? round($explicitWeightGrams, 3)
            : round($baseGoldWeight, 3);

        $densityMultiplier = 1.0;
        $sizeMultiplier = 1.0;

        $karatGoldRate = self::getKaratRate($purity ?: '18KT Gold', $rate24kt);

        $goldValue = round($finalGoldWeight * $karatGoldRate, 2);
        $makingValue = round($finalGoldWeight * $makingChargePerGram, 2);
        $diamondValue = round($diamondStoneValue, 2);

        $subtotal = round($goldValue + $makingValue + $diamondValue, 2);
        $gstAmount = round($subtotal * 0.03, 2);
        $finalPrice = (float) round($subtotal * 1.03);

        $rates = self::getRates();
        $markupPercent = (float) ($rates['mrp_markup_percent'] ?? 20.0);
        $mrpPrice = (float) round($finalPrice * (1 + ($markupPercent / 100)));
        $discountPercent = $mrpPrice > $finalPrice ? (int) round((($mrpPrice - $finalPrice) / $mrpPrice) * 100) : 0;

        return [
            'base_gold_weight'       => $baseGoldWeight,
            'density_multiplier'     => $densityMultiplier,
            'size_multiplier'        => $sizeMultiplier,
            'final_gold_weight'      => $finalGoldWeight,
            'rate_24kt'              => $rate24kt,
            'karat_gold_rate'        => $karatGoldRate,
            'gold_value'             => $goldValue,
            'making_charge_per_gram' => $makingChargePerGram,
            'making_value'           => $makingValue,
            'diamond_stone_value'    => $diamondValue,
            'subtotal'               => $subtotal,
            'gst_rate'               => 3.0,
            'gst_amount'             => $gstAmount,
            'final_price'            => $finalPrice,
            'mrp_price'              => $mrpPrice,
            'discount_percent'       => $discountPercent,
        ];
    }

    /**
     * Calculate price for a specific purity given weight, diamond price, making charge, and discount/mrp.
     * (Backward compatible legacy bridge)
     */
    public static function calculatePurityPrice(
        string $purity,
        float $netWeightGrams,
        float $makingCharge = 0.0,
        string $makingType = 'per_gram',
        float $diamondValue = 0.0,
        ?array $rates = null
    ): array {
        $rates = $rates ?? self::getRates();
        $ratePerGram = self::getRateForPurity($purity, $rates);

        $goldValue = round($netWeightGrams * $ratePerGram, 2);

        $makingValue = $makingType === 'flat'
            ? $makingCharge
            : round($netWeightGrams * ($makingCharge > 0 ? $makingCharge : $rates['making_per_gram']), 2);

        $subtotal = round($goldValue + $makingValue + $diamondValue, 2);
        $gstAmount = round($subtotal * ($rates['gst_rate'] / 100), 2);
        $finalPrice = round($subtotal + $gstAmount);
        $markupPercent = (float) ($rates['mrp_markup_percent'] ?? 20.0);
        $mrpPrice = round($finalPrice * (1 + ($markupPercent / 100)));

        return [
            'purity'         => $purity,
            'rate_per_gram'  => $ratePerGram,
            'gold_weight'    => $netWeightGrams,
            'gold_value'     => $goldValue,
            'making_value'   => $makingValue,
            'diamond_value'  => $diamondValue,
            'subtotal'       => $subtotal,
            'gst_amount'     => $gstAmount,
            'selling_price'  => $finalPrice,
            'mrp_price'      => $mrpPrice,
        ];
    }

    /**
     * Recalculate and update a product's purity prices based on its physical specifications.
     *
     * @param Product $product
     * @param array|null $rates
     * @return bool True if recalculated, false if product is in fixed/manual mode
     */
    public static function recalculateProductPrices(Product $product, ?array $rates = null): bool
    {
        $attributes = $product->attributes ?? [];
        $pricingMode = $attributes['pricing_mode'] ?? 'manual';

        // Extract weight in grams
        $weight = 0.0;
        if (!empty($attributes['gold_weight_grams']) && is_numeric($attributes['gold_weight_grams'])) {
            $weight = (float) $attributes['gold_weight_grams'];
        } elseif (!empty($attributes['gold_weight']) && preg_match('/([\d\.]+)/', (string) $attributes['gold_weight'], $matches)) {
            $weight = (float) $matches[1];
        }

        // Only auto-recalculate if pricing mode is 'dynamic' and gold purities are configured
        if ($pricingMode === 'manual' || empty($attributes['gold_purities']) || $weight <= 0) {
            return false;
        }

        $rates = $rates ?? self::getRates();
        $makingCharge = (float) ($attributes['making_charge'] ?? $rates['making_per_gram']);
        $makingType = (string) ($attributes['making_charge_type'] ?? 'per_gram');
        $diamondValue = (float) ($attributes['diamond_value'] ?? 0.0);

        $selectedPurities = (array) ($attributes['gold_purities'] ?? self::PURITIES);
        $purityPrices = $attributes['purity_prices'] ?? [];
        $purityMrp = $attributes['purity_mrp'] ?? [];

        foreach ($selectedPurities as $purity) {
            $calc = self::calculatePurityPrice(
                $purity,
                $weight,
                $makingCharge,
                $makingType,
                $diamondValue,
                $rates
            );

            $purityPrices[$purity] = $calc['selling_price'];
            $purityMrp[$purity] = $calc['mrp_price'];
        }

        // Recalculate each size variant multi-purity matrix using exact bullion formula
        if (!empty($attributes['size_variants'])) {
            foreach ($attributes['size_variants'] as $idx => $v) {
                $vWeight = (float) ($v['weight_grams'] ?? 0);
                if ($vWeight > 0) {
                    $c14 = self::calculateFinalPrice($vWeight, $makingCharge, $diamondValue, '14KT Gold', null, $rates['rate_24kt'], $vWeight);
                    $c18 = self::calculateFinalPrice($vWeight, $makingCharge, $diamondValue, '18KT Gold', null, $rates['rate_24kt'], $vWeight);
                    $c22 = self::calculateFinalPrice($vWeight, $makingCharge, $diamondValue, '22KT Gold', null, $rates['rate_24kt'], $vWeight);

                    $attributes['size_variants'][$idx]['price_14k'] = $c14['final_price'];
                    $attributes['size_variants'][$idx]['mrp_14k']   = $c14['mrp_price'];

                    $attributes['size_variants'][$idx]['price_18k'] = $c18['final_price'];
                    $attributes['size_variants'][$idx]['mrp_18k']   = $c18['mrp_price'];

                    $attributes['size_variants'][$idx]['price_22k'] = $c22['final_price'];
                    $attributes['size_variants'][$idx]['mrp_22k']   = $c22['mrp_price'];

                    $attributes['size_variants'][$idx]['price']     = $c18['final_price'];
                    $attributes['size_variants'][$idx]['mrp']       = $c18['mrp_price'];
                }
            }
        }

        $attributes['purity_prices'] = $purityPrices;
        $attributes['purity_mrp'] = $purityMrp;
        $attributes['pricing_mode'] = 'dynamic';
        $attributes['gold_weight_grams'] = $weight;
        $attributes['gold_rates_synced_at'] = now()->toDateTimeString();

        // Update default base price from first canonical purity
        $canonical = ['14KT Gold', '18KT Gold', '22KT Gold'];
        $firstPurity = null;
        foreach ($canonical as $c) {
            if (in_array($c, $selectedPurities, true)) {
                $firstPurity = $c;
                break;
            }
        }
        $firstPurity = $firstPurity ?? ($selectedPurities[0] ?? null);

        if ($firstPurity && isset($purityPrices[$firstPurity])) {
            $sellingPrice = (float) $purityPrices[$firstPurity];
            $mrp = isset($purityMrp[$firstPurity]) ? (float) $purityMrp[$firstPurity] : null;

            if ($mrp !== null && $mrp > $sellingPrice) {
                $product->price = $mrp;
                $product->sale_price = $sellingPrice;
            } else {
                $product->price = $sellingPrice;
                $product->sale_price = null;
            }
        }

        $product->attributes = $attributes;
        $product->saveQuietly();

        return true;
    }

    /**
     * Batch sync all dynamic products across the store when gold rate changes.
     *
     * @return array{total_products: int, synced_products: int, skipped: int}
     */
    public static function syncAllStoreProducts(): array
    {
        $rates = self::getRates();
        $products = Product::all();
        $synced = 0;
        $skipped = 0;

        foreach ($products as $product) {
            $updated = self::recalculateProductPrices($product, $rates);
            if ($updated) {
                $synced++;
            } else {
                $skipped++;
            }
        }

        Log::info("Bullion pricing synced: {$synced} products updated, {$skipped} skipped.");

        return [
            'total_products'  => $products->count(),
            'synced_products' => $synced,
            'skipped'         => $skipped,
        ];
    }

    /**
     * Authoritative Server-Side Price Resolution for Any Product with Any Variant Options.
     * Prevents client-side price tampering and guarantees 100% pricing consistency across
     * Catalog Card, Product Detail Page, Add to Bag, Cart Drawer, Cart Page, Checkout, and Payment Gateways.
     *
     * @param Product $product
     * @param array $options
     * @return float
     */
    public static function resolveItemPrice(Product $product, array $options = []): float
    {
        $selectedSize = $options['size'] ?? null;
        $selectedPurity = $options['gold_purity'] ?? null;

        $isDynamic = ($product->base_gold_weight > 0
            && ($product->attributes['pricing_mode'] ?? '') === 'dynamic'
            && !empty($product->gold_purities));

        if ($isDynamic) {
            $purities = (array) $product->gold_purities;
            $purity = $selectedPurity ?: ($purities[0] ?? '18KT Gold');
            $pLower = strtolower($purity);

            // 1. If size is selected, check precalculated size_variants matrix first
            if (!empty($selectedSize)) {
                $sizeVariants = (array) ($product->attributes['size_variants'] ?? []);
                foreach ($sizeVariants as $variant) {
                    if (($variant['size'] ?? '') === $selectedSize) {
                        if (str_contains($pLower, '14') && !empty($variant['price_14k'])) {
                            return (float) $variant['price_14k'];
                        }
                        if (str_contains($pLower, '22') && !empty($variant['price_22k'])) {
                            return (float) $variant['price_22k'];
                        }
                        if (str_contains($pLower, '18') && !empty($variant['price_18k'])) {
                            return (float) $variant['price_18k'];
                        }
                        if (isset($variant['price']) && (float) $variant['price'] > 0) {
                            return (float) $variant['price'];
                        }
                        break;
                    }
                }
            }

            // 2. Otherwise calculate dynamically via Bullion Pricing Engine
            $rates = self::getRates();
            $explicitWeight = null;
            if (!empty($selectedSize)) {
                foreach ((array) ($product->attributes['size_variants'] ?? []) as $sv) {
                    if (($sv['size'] ?? '') === $selectedSize && isset($sv['weight_grams']) && (float) $sv['weight_grams'] > 0) {
                        $explicitWeight = (float) $sv['weight_grams'];
                        break;
                    }
                }
            }

            $calc = self::calculateFinalPrice(
                $product->base_gold_weight,
                $product->making_charge_per_gram,
                $product->diamond_stone_value,
                $purity,
                $selectedSize ?: 'Size 12',
                $rates['rate_24kt'],
                $explicitWeight
            );

            return (float) $calc['final_price'];
        }

        // Standard / Manual Pricing
        $price = (float) $product->effective_price;

        if (!empty($selectedSize)) {
            $sizeVariants = (array) ($product->attributes['size_variants'] ?? []);
            $pLower = strtolower($selectedPurity ?? '');
            foreach ($sizeVariants as $variant) {
                if (($variant['size'] ?? '') === $selectedSize) {
                    if (str_contains($pLower, '14') && !empty($variant['price_14k'])) {
                        $price = (float) $variant['price_14k'];
                    } elseif (str_contains($pLower, '22') && !empty($variant['price_22k'])) {
                        $price = (float) $variant['price_22k'];
                    } elseif (!empty($variant['price_18k'])) {
                        $price = (float) $variant['price_18k'];
                    } elseif (isset($variant['price']) && (float) $variant['price'] > 0) {
                        $price = (float) $variant['price'];
                    }
                    break;
                }
            }
        }

        // Apply Gold Purity Price if manually configured
        if (!empty($selectedPurity)) {
            $purityPrices = $product->attributes['purity_prices'] ?? [];
            if (isset($purityPrices[$selectedPurity]) && $purityPrices[$selectedPurity] !== null && $purityPrices[$selectedPurity] !== '') {
                $val = (float) $purityPrices[$selectedPurity];
                if ($val >= $price * 0.5 && $val > 0) {
                    $price = $val;
                } else {
                    $price += $val;
                }
            } else {
                $p = strtolower($selectedPurity);
                if (str_contains($p, '18k')) {
                    $price = round(($price * 1.18) / 100) * 100;
                } elseif (str_contains($p, '22k')) {
                    $price = round(($price * 1.35) / 100) * 100;
                }
            }
        }

        return $price;
    }

    /**
     * Authoritative Server-Side MRP Resolution for Any Product with Any Variant Options.
     *
     * @param Product $product
     * @param array $options
     * @return float
     */
    public static function resolveItemMrp(Product $product, array $options = []): float
    {
        $selectedSize = $options['size'] ?? null;
        $selectedPurity = $options['gold_purity'] ?? null;
        $sellingPrice = self::resolveItemPrice($product, $options);

        if (!empty($selectedSize)) {
            $sizeVariants = (array) ($product->attributes['size_variants'] ?? []);
            $pLower = strtolower($selectedPurity ?? '');
            foreach ($sizeVariants as $variant) {
                if (($variant['size'] ?? '') === $selectedSize) {
                    if (str_contains($pLower, '14') && !empty($variant['mrp_14k'])) {
                        return (float) $variant['mrp_14k'];
                    }
                    if (str_contains($pLower, '22') && !empty($variant['mrp_22k'])) {
                        return (float) $variant['mrp_22k'];
                    }
                    if (str_contains($pLower, '18') && !empty($variant['mrp_18k'])) {
                        return (float) $variant['mrp_18k'];
                    }
                    if (isset($variant['mrp']) && (float) $variant['mrp'] > 0) {
                        return (float) $variant['mrp'];
                    }
                    break;
                }
            }
        }

        $rates = self::getRates();
        $markupPercent = (float) ($rates['mrp_markup_percent'] ?? 20.0);
        return (float) round($sellingPrice * (1 + ($markupPercent / 100)));
    }
}

