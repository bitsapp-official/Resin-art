<?php

namespace App\Services;

use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    /**
     * Prevents recursion during auto-sync
     */
    protected static bool $isSyncing = false;
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
        // Auto self-refresh if provider has published next update OR if rates are older than 4 hours
        if (!self::$isSyncing) {
            self::$isSyncing = true;
            try {
                $lastSync = SiteSetting::get('currency_rates_last_synced_at', null);
                $nextUnix = (int) SiteSetting::get('currency_api_next_update_unix', 0);

                $shouldSync = false;
                if ($nextUnix > 0 && time() >= $nextUnix) {
                    $shouldSync = true;
                } elseif (!$lastSync || Carbon::parse($lastSync)->diffInHours(now()) >= 4) {
                    $shouldSync = true;
                }

                if ($shouldSync) {
                    if (Cache::add('currency_auto_sync_lock', true, 600)) {
                        self::syncLiveRates();
                    }
                }
            } catch (\Throwable $e) {
                // Silently ignore to never break UI
            } finally {
                self::$isSyncing = false;
            }
        }

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
        SiteSetting::set('currency_rates_last_synced_at', now()->toIso8601String());

        // Also update individual keys for backwards compatibility if needed
        foreach ($currencies as $item) {
            $code = strtolower($item['code'] ?? '');
            if (!empty($code) && $code !== 'inr' && isset($item['rate'])) {
                SiteSetting::set('currency_rate_' . $code, (string) $item['rate']);
            }
        }
    }

    /**
     * Fetch live INR rates from market APIs and sync configured currencies.
     * Direct conversion format: 1 Foreign Currency Unit = X INR (e.g. 1 USD = ₹95.04)
     *
     * @return array Updated currencies dictionary
     */
    public static function syncLiveRates(): array
    {
        $rawRates = [];

        // 1. Primary API: open.er-api.com
        try {
            $response = Http::timeout(10)->get('https://open.er-api.com/v6/latest/INR');
            if ($response->successful()) {
                $rates = $response->json('rates');
                if (is_array($rates) && !empty($rates)) {
                    $rawRates = $rates;
                }

                $lastUpdate = $response->json('time_last_update_utc');
                $nextUpdateUnix = $response->json('time_next_update_unix');
                $nextUpdateUtc = $response->json('time_next_update_utc');

                if ($lastUpdate) {
                    SiteSetting::set('currency_api_last_update_utc', (string) $lastUpdate);
                }
                if ($nextUpdateUnix) {
                    SiteSetting::set('currency_api_next_update_unix', (string) $nextUpdateUnix);
                }
                if ($nextUpdateUtc) {
                    SiteSetting::set('currency_api_next_update_utc', (string) $nextUpdateUtc);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('CurrencyService primary live rate sync failed: ' . $e->getMessage());
        }

        // 2. Fallback API: jsdelivr / fawazahmed0 currency-api
        if (empty($rawRates)) {
            try {
                $fallback = Http::timeout(10)->get('https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/inr.json');
                if ($fallback->successful()) {
                    $inrRates = $fallback->json('inr');
                    if (is_array($inrRates) && !empty($inrRates)) {
                        $rawRates = $inrRates;
                    }
                }
            } catch (\Throwable $e) {
                Log::error('CurrencyService fallback live rate sync failed: ' . $e->getMessage());
            }
        }

        // If both APIs failed, return current currencies unchanged
        if (empty($rawRates)) {
            return self::getAllCurrencies();
        }

        // Normalize rates array keys to uppercase (e.g. USD, AED, EUR)
        $normalizedRates = [];
        foreach ($rawRates as $codeKey => $rateVal) {
            $normalizedRates[strtoupper((string) $codeKey)] = (float) $rateVal;
        }

        // 3. Update existing configured currencies
        $currencies = self::getAllCurrencies();
        $hasUpdates = false;

        foreach ($currencies as $code => $info) {
            if ($code === 'INR' || !empty($info['is_base'])) {
                continue;
            }

            if (isset($normalizedRates[$code]) && $normalizedRates[$code] > 0) {
                // Formula: 1 Foreign Unit = X INR (1 / apiResponseRate)
                $liveRate = (float) round(1 / $normalizedRates[$code], 2);
                $currencies[$code]['rate'] = $liveRate;
                $hasUpdates = true;
            }
        }

        // 4. Persist updated rates if any changed
        if ($hasUpdates) {
            self::saveAllCurrencies($currencies);
        }

        return $currencies;
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
     * Get the Forex Risk Buffer Percentage (defaults to 0 for direct conversion)
     */
    public static function getHedgingBufferPercent(): float
    {
        return 0.0;
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
     * Get direct conversion rate for a currency against INR (No forex buffer/markup)
     */
    public static function getEffectiveRate(?string $code = null): float
    {
        $code = strtoupper($code ?: self::getUserCurrency());
        if ($code === 'INR') {
            return 1.0;
        }

        return max(0.0001, self::getRawRate($code));
    }

    /**
     * Convert an INR amount into the target currency using direct conversion rate
     */
    public static function convert(float $inrAmount, ?string $targetCurrency = null): float
    {
        $targetCurrency = strtoupper($targetCurrency ?: self::getUserCurrency());
        if ($targetCurrency === 'INR' || $inrAmount <= 0) {
            return (float) $inrAmount;
        }

        $rate = self::getEffectiveRate($targetCurrency);
        $converted = $inrAmount / $rate;

        return (float) round($converted);
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
