<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Log;

class BullionPricingService
{
    /**
     * Standard gold & bullion purities handled in fine jewellery.
     */
    public const PURITIES = ['10KT Gold', '14KT Gold', '18KT Gold', '22KT Gold', '925 Silver'];

    /**
     * Certified Natural Diamond Clarities supported across fine jewellery collections.
     */
    public const DIAMOND_CLARITIES = ['VVS1', 'VVS2', 'VS1', 'VS2'];

    /**
     * Get current live bullion & diamond rates from SiteSettings.
     *
     * @return array{
     *     rate_24kt: float,
     *     rate_22kt: float,
     *     rate_18kt: float,
     *     rate_14kt: float,
     *     rate_10kt: float,
     *     rate_silver_925: float,
     *     diamond_rate_vvs1: float,
     *     diamond_rate_vvs2: float,
     *     diamond_rate_vs1: float,
     *     diamond_rate_vs2: float,
     *     making_per_gram: float,
     *     gst_rate: float,
     *     mrp_markup_percent: float,
     *     updated_at: string
     * }
     */
    public static function getRates(): array
    {
        $rate22kt = (float) SiteSetting::get('gold_rate_22kt', 6966.0);
        $rate18kt = (float) SiteSetting::get('gold_rate_18kt', 5700.0);
        $rate14kt = (float) SiteSetting::get('gold_rate_14kt', 4433.0);
        $rate10kt = (float) SiteSetting::get('gold_rate_10kt', 3167.0);
        $rateSilver925 = (float) SiteSetting::get('silver_rate_925', 95.0);
        $rate24kt = (float) (SiteSetting::get('gold_rate_24kt') ?: round($rate22kt * (24 / 22), 2));

        $rateDiamondVvs1 = (float) SiteSetting::get('diamond_rate_vvs1', 110000.0);
        $rateDiamondVvs2 = (float) SiteSetting::get('diamond_rate_vvs2', 90000.0);
        $rateDiamondVs1  = (float) SiteSetting::get('diamond_rate_vs1', 75000.0);
        $rateDiamondVs2  = (float) SiteSetting::get('diamond_rate_vs2', 60000.0);

        $makingPerGram = (float) SiteSetting::get('gold_default_making_per_gram', 650);
        $gstRate = (float) SiteSetting::get('gold_gst_rate', 3.0);
        $mrpMarkupPercent = (float) SiteSetting::get('gold_mrp_markup_percent', 20.0);
        $updatedAt = (string) SiteSetting::get('gold_rates_updated_at', now()->toDateTimeString());

        return [
            'rate_24kt'           => $rate24kt,
            'rate_22kt'           => $rate22kt,
            'rate_18kt'           => $rate18kt,
            'rate_14kt'           => $rate14kt,
            'rate_10kt'           => $rate10kt,
            'rate_silver_925'     => $rateSilver925,
            'diamond_rate_vvs1'   => $rateDiamondVvs1,
            'diamond_rate_vvs2'   => $rateDiamondVvs2,
            'diamond_rate_vs1'    => $rateDiamondVs1,
            'diamond_rate_vs2'    => $rateDiamondVs2,
            'making_per_gram'     => $makingPerGram,
            'gst_rate'            => $gstRate,
            'mrp_markup_percent'  => $mrpMarkupPercent,
            'updated_at'          => $updatedAt,
        ];
    }

    /**
     * Return rate per carat for a specific Diamond Clarity (VVS1, VVS2, VS1, VS2).
     */
    public static function getDiamondRateForClarity(string $clarity, ?array $rates = null): float
    {
        $rates = $rates ?? self::getRates();
        $c = strtoupper(trim($clarity));

        if (str_contains($c, 'VVS1')) {
            return (float) ($rates['diamond_rate_vvs1'] ?? 110000.0);
        }
        if (str_contains($c, 'VVS2')) {
            return (float) ($rates['diamond_rate_vvs2'] ?? 90000.0);
        }
        if (str_contains($c, 'VS1')) {
            return (float) ($rates['diamond_rate_vs1'] ?? 75000.0);
        }
        if (str_contains($c, 'VS2')) {
            return (float) ($rates['diamond_rate_vs2'] ?? 60000.0);
        }

        return (float) ($rates['diamond_rate_vvs1'] ?? 110000.0);
    }

    /**
     * Return rate per gram for a specific Karat or Silver purity.
     */
    public static function getRateForPurity(string $purity, ?array $rates = null): float
    {
        $rates = $rates ?? self::getRates();
        $p = strtolower(trim($purity));

        if (str_contains($p, 'silver') || str_contains($p, '925')) {
            return (float) ($rates['rate_silver_925'] ?? 95.0);
        }
        if (str_contains($p, '22k')) {
            return (float) ($rates['rate_22kt'] ?? 6966.0);
        }
        if (str_contains($p, '18k')) {
            return (float) ($rates['rate_18kt'] ?? round(($rates['rate_24kt'] ?? 7600.0) * (18 / 24), 2));
        }
        if (str_contains($p, '14k')) {
            return (float) ($rates['rate_14kt'] ?? round(($rates['rate_24kt'] ?? 7600.0) * (14 / 24), 2));
        }
        if (str_contains($p, '10k')) {
            return (float) ($rates['rate_10kt'] ?? round(($rates['rate_24kt'] ?? 7600.0) * (10 / 24), 2));
        }

        return (float) ($rates['rate_18kt'] ?? 5700.0);
    }

    /**
     * Compute exact metallurgical density multiplier:
     * 10K / 10KT = 0.885
     * 14K / 14KT = 1.000 (Base density anchor)
     * 18K / 18KT = 1.185 (+18.5% weight due to metal density)
     * 22K / 22KT = 1.365 (+36.5% weight due to metal density)
     * 24K / 24KT = 1.485 (Pure 999 density)
     * Silver 925 = 1.000 (Fine silver anchor)
     */
    public static function getDensityMultiplier(?string $purity): float
    {
        if (empty($purity)) {
            return 1.185;
        }

        $p = strtolower(trim($purity));
        if (str_contains($p, 'silver') || str_contains($p, '925')) {
            return 1.000;
        }
        if (str_contains($p, '10k')) {
            return 0.885;
        }
        if (str_contains($p, '14k')) {
            return 1.000;
        }
        if (str_contains($p, '22k')) {
            return 1.365;
        }
        if (str_contains($p, '24k')) {
            return 1.485;
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
     * Calculate Karat Gold or Silver Rate from 24K Live Rate / Silver Rate:
     * Karat Gold Rate = 24K Live Rate * (Karat / 24)
     * Silver Rate = Configured 925 Silver Rate
     */
    public static function getKaratRate(string $purity, ?float $rate24kt = null): float
    {
        $rates = self::getRates();
        $base24kt = (float) ($rates['rate_24kt'] ?: 7600.0);
        $p = strtolower(trim($purity));

        if (str_contains($p, 'silver') || str_contains($p, '925')) {
            return (float) ($rates['rate_silver_925'] ?? 95.0);
        }

        // If an explicit custom 24k rate is passed that differs from base settings, scale mathematically
        if ($rate24kt !== null && $rate24kt > 0 && abs($rate24kt - $base24kt) > 0.01) {
            if (str_contains($p, '24k')) {
                return round($rate24kt, 2);
            }
            if (str_contains($p, '22k')) {
                return round($rate24kt * (22.0 / 24.0), 2);
            }
            if (str_contains($p, '18k')) {
                return round($rate24kt * (18.0 / 24.0), 2);
            }
            if (str_contains($p, '14k')) {
                return round($rate24kt * (14.0 / 24.0), 2);
            }
            if (str_contains($p, '10k')) {
                return round($rate24kt * (10.0 / 24.0), 2);
            }
            return round($rate24kt * (18.0 / 24.0), 2);
        }

        // Use the exact Karat gold rate configured by admin in Daily Gold & Bullion Rates
        if (str_contains($p, '24k')) {
            return (float) ($rates['rate_24kt'] ?: 7600.0);
        }
        if (str_contains($p, '22k')) {
            return (float) ($rates['rate_22kt'] ?: round($base24kt * (22.0 / 24.0), 2));
        }
        if (str_contains($p, '18k')) {
            return (float) ($rates['rate_18kt'] ?: round($base24kt * (18.0 / 24.0), 2));
        }
        if (str_contains($p, '14k')) {
            return (float) ($rates['rate_14kt'] ?: round($base24kt * (14.0 / 24.0), 2));
        }
        if (str_contains($p, '10k')) {
            return (float) ($rates['rate_10kt'] ?: round($base24kt * (10.0 / 24.0), 2));
        }
        return (float) ($rates['rate_18kt'] ?: round($base24kt * (18.0 / 24.0), 2));
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
        ?float $explicitWeightGrams = null,
        ?float $profitMarginPercent = 0.0,
        ?float $diamondCaratWeight = null,
        ?string $diamondClarity = null
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

        // Dynamic Diamond Calculation:
        // If diamond carat weight is provided (> 0), compute diamond value as: diamondCaratWeight * clarity_rate
        $clarityRate = null;
        if ($diamondCaratWeight !== null && $diamondCaratWeight > 0) {
            $clarity = $diamondClarity ?: 'VVS1';
            $clarityRate = self::getDiamondRateForClarity($clarity, $rates);
            $diamondValue = round($diamondCaratWeight * $clarityRate, 2);
        } else {
            $diamondValue = round($diamondStoneValue, 2);
        }

        $subtotal = round($goldValue + $makingValue + $diamondValue, 2);
        $gstAmount = round($subtotal * 0.03, 2);
        $costWithGst = round($subtotal * 1.03, 2);

        $marginPercent = (float) ($profitMarginPercent ?: 0.0);
        $profitAmount = round($costWithGst * ($marginPercent / 100), 2);
        $finalPrice = (float) round($costWithGst + $profitAmount);

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
            'diamond_carat_weight'   => $diamondCaratWeight,
            'diamond_clarity'        => $diamondClarity,
            'diamond_clarity_rate'   => $clarityRate,
            'subtotal'               => $subtotal,
            'gst_rate'               => 3.0,
            'gst_amount'             => $gstAmount,
            'cost_with_gst'          => $costWithGst,
            'profit_margin_percent'  => $marginPercent,
            'profit_amount'          => $profitAmount,
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
        $profitMargin = (float) ($attributes['profit_margin_percent'] ?? 0.0);

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
                    $c10 = self::calculateFinalPrice($vWeight, $makingCharge, $diamondValue, '10KT Gold', null, $rates['rate_24kt'], $vWeight, $profitMargin);
                    $c14 = self::calculateFinalPrice($vWeight, $makingCharge, $diamondValue, '14KT Gold', null, $rates['rate_24kt'], $vWeight, $profitMargin);
                    $c18 = self::calculateFinalPrice($vWeight, $makingCharge, $diamondValue, '18KT Gold', null, $rates['rate_24kt'], $vWeight, $profitMargin);
                    $c22 = self::calculateFinalPrice($vWeight, $makingCharge, $diamondValue, '22KT Gold', null, $rates['rate_24kt'], $vWeight, $profitMargin);
                    $cSil = self::calculateFinalPrice($vWeight, $makingCharge, $diamondValue, '925 Silver', null, $rates['rate_24kt'], $vWeight, $profitMargin);

                    $attributes['size_variants'][$idx]['price_10k']    = $c10['final_price'];
                    $attributes['size_variants'][$idx]['price_14k']    = $c14['final_price'];
                    $attributes['size_variants'][$idx]['price_18k']    = $c18['final_price'];
                    $attributes['size_variants'][$idx]['price_22k']    = $c22['final_price'];
                    $attributes['size_variants'][$idx]['price_silver'] = $cSil['final_price'];
                    $attributes['size_variants'][$idx]['price']        = $c18['final_price'];

                    // Retain manual cut/mrp prices if set by user; do not auto-force synthetic markup
                    $attributes['size_variants'][$idx]['mrp_10k']    = !empty($v['mrp_10k']) ? (float) $v['mrp_10k'] : null;
                    $attributes['size_variants'][$idx]['mrp_14k']    = !empty($v['mrp_14k']) ? (float) $v['mrp_14k'] : null;
                    $attributes['size_variants'][$idx]['mrp_18k']    = !empty($v['mrp_18k']) ? (float) $v['mrp_18k'] : null;
                    $attributes['size_variants'][$idx]['mrp_22k']    = !empty($v['mrp_22k']) ? (float) $v['mrp_22k'] : null;
                    $attributes['size_variants'][$idx]['mrp_silver'] = !empty($v['mrp_silver']) ? (float) $v['mrp_silver'] : null;
                    $attributes['size_variants'][$idx]['mrp']        = !empty($v['mrp_18k']) ? (float) $v['mrp_18k'] : null;
                }
            }
        }

        $attributes['purity_prices'] = $purityPrices;
        $attributes['purity_mrp'] = $purityMrp;
        $attributes['pricing_mode'] = 'dynamic';
        $attributes['gold_weight_grams'] = $weight;
        $attributes['gold_rates_synced_at'] = now()->toDateTimeString();

        // Update default base price from first canonical purity
        $canonical = ['10KT Gold', '14KT Gold', '18KT Gold', '22KT Gold', '925 Silver'];
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
        $selectedClarity = $options['diamond_clarity'] ?? ($options['clarity'] ?? null);
        $diamondCarat = $product->diamond_carat_weight;

        $isDynamic = ($product->base_gold_weight > 0
            && ($product->attributes['pricing_mode'] ?? '') === 'dynamic'
            && !empty($product->gold_purities));

        if ($isDynamic) {
            $purities = (array) $product->gold_purities;
            $purity = $selectedPurity ?: ($purities[0] ?? '18KT Gold');
            $pLower = strtolower($purity);

            // 1. If size is selected AND diamond carat is not dynamic, check precalculated size_variants matrix first
            if (!empty($selectedSize) && $diamondCarat <= 0) {
                $sizeVariants = (array) ($product->attributes['size_variants'] ?? []);
                foreach ($sizeVariants as $variant) {
                    if (($variant['size'] ?? '') === $selectedSize) {
                        if ((str_contains($pLower, 'silver') || str_contains($pLower, '925')) && !empty($variant['price_silver'])) {
                            return (float) $variant['price_silver'];
                        }
                        if (str_contains($pLower, '24') && !empty($variant['price_24k'])) {
                            return (float) $variant['price_24k'];
                        }
                        if (str_contains($pLower, '22') && !empty($variant['price_22k'])) {
                            return (float) $variant['price_22k'];
                        }
                        if (str_contains($pLower, '18') && !empty($variant['price_18k'])) {
                            return (float) $variant['price_18k'];
                        }
                        if (str_contains($pLower, '14') && !empty($variant['price_14k'])) {
                            return (float) $variant['price_14k'];
                        }
                        if (str_contains($pLower, '10') && !empty($variant['price_10k'])) {
                            return (float) $variant['price_10k'];
                        }
                        if (isset($variant['price']) && (float) $variant['price'] > 0) {
                            return (float) $variant['price'];
                        }
                        break;
                    }
                }
            }

            // 2. Otherwise calculate dynamically via Bullion & Diamond Pricing Engine
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

            $profitMargin = (float) $product->profit_margin_percent;
            $clarityToUse = $selectedClarity ?: (!empty($product->diamond_clarities) ? $product->diamond_clarities[0] : 'VVS1');

            $calc = self::calculateFinalPrice(
                $product->base_gold_weight,
                $product->making_charge_per_gram,
                $product->diamond_stone_value,
                $purity,
                $selectedSize ?: 'Size 12',
                $rates['rate_24kt'],
                $explicitWeight,
                $profitMargin,
                $diamondCarat > 0 ? $diamondCarat : null,
                $clarityToUse
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
                    if ((str_contains($pLower, 'silver') || str_contains($pLower, '925')) && !empty($variant['price_silver'])) {
                        $price = (float) $variant['price_silver'];
                    } elseif (str_contains($pLower, '24') && !empty($variant['price_24k'])) {
                        $price = (float) $variant['price_24k'];
                    } elseif (str_contains($pLower, '22') && !empty($variant['price_22k'])) {
                        $price = (float) $variant['price_22k'];
                    } elseif (str_contains($pLower, '18') && !empty($variant['price_18k'])) {
                        $price = (float) $variant['price_18k'];
                    } elseif (str_contains($pLower, '14') && !empty($variant['price_14k'])) {
                        $price = (float) $variant['price_14k'];
                    } elseif (str_contains($pLower, '10') && !empty($variant['price_10k'])) {
                        $price = (float) $variant['price_10k'];
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
                if (str_contains($p, 'silver') || str_contains($p, '925')) {
                    $price = round(($price * 0.15) / 100) * 100;
                } elseif (str_contains($p, '10k')) {
                    $price = round(($price * 0.65) / 100) * 100;
                } elseif (str_contains($p, '14k')) {
                    $price = round(($price * 0.85) / 100) * 100;
                } elseif (str_contains($p, '18k')) {
                    $price = round(($price * 1.18) / 100) * 100;
                } elseif (str_contains($p, '22k')) {
                    $price = round(($price * 1.35) / 100) * 100;
                } elseif (str_contains($p, '24k')) {
                    $price = round(($price * 1.48) / 100) * 100;
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
                    if ((str_contains($pLower, 'silver') || str_contains($pLower, '925')) && !empty($variant['mrp_silver'])) {
                        return (float) $variant['mrp_silver'];
                    }
                    if (str_contains($pLower, '24') && !empty($variant['mrp_24k'])) {
                        return (float) $variant['mrp_24k'];
                    }
                    if (str_contains($pLower, '22') && !empty($variant['mrp_22k'])) {
                        return (float) $variant['mrp_22k'];
                    }
                    if (str_contains($pLower, '18') && !empty($variant['mrp_18k'])) {
                        return (float) $variant['mrp_18k'];
                    }
                    if (str_contains($pLower, '14') && !empty($variant['mrp_14k'])) {
                        return (float) $variant['mrp_14k'];
                    }
                    if (str_contains($pLower, '10') && !empty($variant['mrp_10k'])) {
                        return (float) $variant['mrp_10k'];
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

