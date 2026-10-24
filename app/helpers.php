<?php

use App\Services\CurrencyService;

if (!function_exists('currency_format')) {
    /**
     * Format an authoritative INR amount into customer's active luxury currency.
     */
    function currency_format(float $inrAmount, ?string $currency = null): string
    {
        return CurrencyService::format($inrAmount, $currency);
    }
}

if (!function_exists('currency_convert')) {
    /**
     * Convert an authoritative INR amount into numerical value in target currency.
     */
    function currency_convert(float $inrAmount, ?string $currency = null): float
    {
        return CurrencyService::convert($inrAmount, $currency);
    }
}

if (!function_exists('currency_current')) {
    /**
     * Get active currency code (e.g. 'INR', 'USD', 'AED').
     */
    function currency_current(): string
    {
        return CurrencyService::getUserCurrency();
    }
}

if (!function_exists('currency_info')) {
    /**
     * Get active or specified currency info array.
     */
    function currency_info(?string $currency = null): array
    {
        return CurrencyService::getCurrencyInfo($currency);
    }
}
