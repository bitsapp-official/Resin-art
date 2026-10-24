<?php

namespace App\Services;

use App\Models\SiteSetting;

class CurrencyService
{
    /**
     * Default luxury currencies configuration
     */
    public const DEFAULT_CURRENCIES = [
        'INR' => [
            'code'           => 'INR',
            'symbol'         => '₹',
            'name'           => 'Indian Rupee',
            'flag'           => '🇮🇳',
            'rate'           => 1.0,
            'is_base'        => true,
            'is_active'      => true,
            'format_pattern' => '₹%s',
        ],
        'USD' => [
            'code'           => 'USD',
            'symbol'         => '$',
            'name'           => 'US Dollar',
            'flag'           => '🇺🇸',
            'rate'           => 86.50,
            'is_base'        => false,
            'is_active'      => true,
            'format_pattern' => '$%s',
        ],
        'AED' => [
            'code'           => 'AED',
            'symbol'         => 'AED',
            'name'           => 'UAE Dirham',
            'flag'           => '🇦🇪',
            'rate'           => 23.55,
            'is_base'        => false,
            'is_active'      => true,
            'format_pattern' => 'AED %s',
        ],
        'GBP' => [
            'code'           => 'GBP',
            'symbol'         => '£',
            'name'           => 'British Pound',
            'flag'           => '🇬🇧',
            'rate'           => 110.20,
            'is_base'        => false,
            'is_active'      => true,
            'format_pattern' => '£%s',
        ],
        'EUR' => [
            'code'           => 'EUR',
            'symbol'         => '€',
            'name'           => 'Euro',
            'flag'           => '🇪🇺',
            'rate'           => 94.10,
            'is_base'        => false,
            'is_active'      => true,
            'format_pattern' => '€%s',
        ],
        'CAD' => [
            'code'           => 'CAD',
            'symbol'         => 'CA$',
            'name'           => 'Canadian Dollar',
            'flag'           => '🇨🇦',
            'rate'           => 62.40,
            'is_base'        => false,
            'is_active'      => true,
            'format_pattern' => 'CA$ %s',
        ],
        'AUD' => [
            'code'           => 'AUD',
            'symbol'         => 'AU$',
            'name'           => 'Australian Dollar',
            'flag'           => '🇦🇺',
            'rate'           => 56.80,
            'is_base'        => false,
            'is_active'      => true,
            'format_pattern' => 'AU$ %s',
        ],
    ];

    /**
     * Get all currencies configured in the system (from SiteSetting or defaults)
     *
     * @return array<string, array{
     *     code: string,
     *     symbol: string,
     *     name: string,
     *     flag: string,
     *     rate: float,
     *     is_base: bool,
     *     is_active: bool,
     *     format_pattern: string
     * }>
     */
    public static function getAllCurrencies(): array
    {
        $raw = SiteSetting::get('dynamic_currencies_config', null);
        if (!empty($raw)) {
            $saved = is_array($raw) ? $raw : json_decode((string) $raw, true);
            if (is_array($saved) && !empty($saved)) {
                $mapped = [];
                foreach ($saved as $key => $item) {
                    $code = strtoupper(trim($item['code'] ?? (is_string($key) ? $key : '')));
                    if (empty($code)) continue;

                    $symbol = trim($item['symbol'] ?? ($code === 'INR' ? '₹' : ($code . ' ')));
                    $pattern = $item['format_pattern'] ?? ($code === 'INR' ? '₹%s' : (mb_strlen($symbol) <= 1 ? "{$symbol}%s" : "{$symbol} %s"));
                    $mapped[$code] = [
                        'code'           => $code,
                        'name'           => trim($item['name'] ?? $code),
                        'symbol'         => $symbol,
                        'flag'           => trim($item['flag'] ?? '🌐'),
                        'rate'           => $code === 'INR' ? 1.0 : (float) ($item['rate'] ?? 1.0),
                        'is_base'        => $code === 'INR',
                        'is_active'      => !isset($item['is_active']) || (bool) $item['is_active'],
                        'format_pattern' => $pattern,
                    ];
                }

                // Ensure base INR is always present
                if (!isset($mapped['INR'])) {
                    $mapped = array_merge(['INR' => self::DEFAULT_CURRENCIES['INR']], $mapped);
                } else {
                    $mapped['INR']['is_base'] = true;
                    $mapped['INR']['rate'] = 1.0;
                }

                return $mapped;
            }
        }

        // If no custom dynamic config saved yet, use default luxury currencies
        $result = self::DEFAULT_CURRENCIES;
        foreach ($result as $code => $info) {
            if ($code !== 'INR') {
                $savedRate = SiteSetting::get('currency_rate_' . strtolower($code), null);
                if ($savedRate !== null) {
                    $result[$code]['rate'] = (float) $savedRate;
                }
            }
        }

        return $result;
    }

    /**
     * Get only active enabled currencies for customer storefront selection
     */
    public static function getActiveCurrencies(): array
    {
        $all = self::getAllCurrencies();
        return array_filter($all, fn($item) => !empty($item['is_active']));
    }

    /**
     * Save dynamic currencies list into SiteSettings
     */
    public static function saveAllCurrencies(array $currencies): void
    {
        SiteSetting::set('dynamic_currencies_config', json_encode(array_values($currencies)));

        // Also update individual keys for backwards compatibility if needed
        foreach ($currencies as $item) {
            $code = strtolower($item['code'] ?? '');
            if (!empty($code) && $code !== 'inr' && isset($item['rate'])) {
                SiteSetting::set('currency_rate_' . $code, (string) $item['rate']);
            }
        }
    }

    /**
     * Get the active currency code from session (defaults to INR)
     */
    public static function getUserCurrency(): string
    {
        $curr = strtoupper((string) session('user_currency', 'INR'));
        $active = self::getActiveCurrencies();
        return array_key_exists($curr, $active) ? $curr : 'INR';
    }

    /**
     * Set active user currency in session
     */
    public static function setUserCurrency(string $currency): string
    {
        $currency = strtoupper(trim($currency));
        $active = self::getActiveCurrencies();
        if (array_key_exists($currency, $active)) {
            session(['user_currency' => $currency]);
            return $currency;
        }
        session(['user_currency' => 'INR']);
        return 'INR';
    }

    /**
     * Get metadata for active or specified currency
     */
    public static function getCurrencyInfo(?string $code = null): array
    {
        $code = strtoupper($code ?: self::getUserCurrency());
        $all = self::getAllCurrencies();
        return $all[$code] ?? self::DEFAULT_CURRENCIES['INR'];
    }

    /**
     * Get the Forex Risk Buffer Percentage from SiteSettings (default 3.5%)
     */
    public static function getHedgingBufferPercent(): float
    {
        return (float) SiteSetting::get('currency_hedging_buffer_percent', 3.5);
    }

    /**
     * Get raw configured rate for a currency against INR
     */
    public static function getRawRate(string $code): float
    {
        $code = strtoupper($code);
        if ($code === 'INR') {
            return 1.0;
        }
        $info = self::getCurrencyInfo($code);
        return (float) ($info['rate'] ?? 86.50);
    }

    /**
     * Get effective safe conversion rate (accounting for merchant hedging buffer)
     * Effective Rate = Raw Rate * (1 - (Buffer% / 100))
     */
    public static function getEffectiveRate(?string $code = null): float
    {
        $code = strtoupper($code ?: self::getUserCurrency());
        if ($code === 'INR') {
            return 1.0;
        }

        $rawRate = self::getRawRate($code);
        $buffer = self::getHedgingBufferPercent();
        
        $effective = $rawRate * (1.0 - ($buffer / 100.0));
        return max(0.0001, round($effective, 4));
    }

    /**
     * Convert an INR amount into the target currency with luxury charm whole-number rounding
     */
    public static function convert(float $inrAmount, ?string $targetCurrency = null): float
    {
        $targetCurrency = strtoupper($targetCurrency ?: self::getUserCurrency());
        if ($targetCurrency === 'INR' || $inrAmount <= 0) {
            return (float) $inrAmount;
        }

        $effectiveRate = self::getEffectiveRate($targetCurrency);
        $converted = $inrAmount / $effectiveRate;

        return (float) ceil($converted);
    }

    /**
     * Format an INR amount into customer's active luxury currency string
     */
    public static function format(float $inrAmount, ?string $targetCurrency = null): string
    {
        $targetCurrency = strtoupper($targetCurrency ?: self::getUserCurrency());
        $info = self::getCurrencyInfo($targetCurrency);
        $val = self::convert($inrAmount, $targetCurrency);

        if ($targetCurrency === 'INR') {
            return '₹' . number_format($val);
        }

        $pattern = $info['format_pattern'] ?? '%s';
        return sprintf($pattern, number_format($val));
    }

    /**
     * Get active frontend payload for Alpine.js dynamic price recalculation
     */
    public static function getClientPayload(): array
    {
        $code = self::getUserCurrency();
        $info = self::getCurrencyInfo($code);
        $rate = self::getEffectiveRate($code);

        return [
            'code'           => $code,
            'symbol'         => $info['symbol'],
            'flag'           => $info['flag'],
            'rate'           => $rate,
            'is_inr'         => $code === 'INR',
            'format_pattern' => $info['format_pattern'],
        ];
    }
}
