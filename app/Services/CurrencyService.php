<?php

namespace App\Services;

class CurrencyService
{
    public const USD = 'USD';
    public const BDT = 'BDT';

    public const USD_RATE = 1.0;
    public const BDT_RATE = 120.0; // 1 USD = 120 BDT

    /**
     * Get the active currency from session, cookie, or default to USD.
     */
    public static function getCurrency(): string
    {
        $sessionCurr = session('currency');
        if ($sessionCurr && in_array($sessionCurr, [self::USD, self::BDT])) {
            return $sessionCurr;
        }

        $cookieCurr = request()->cookie('sbl_currency');
        if ($cookieCurr && in_array($cookieCurr, [self::USD, self::BDT])) {
            return $cookieCurr;
        }

        return self::USD;
    }

    /**
     * Set the active currency in session.
     */
    public static function setCurrency(string $code): string
    {
        $code = strtoupper(trim($code));
        if (!in_array($code, [self::USD, self::BDT])) {
            $code = self::USD;
        }

        session(['currency' => $code]);
        return $code;
    }

    /**
     * Get exchange rate relative to USD.
     */
    public static function getRate(string $currency = null): float
    {
        $currency = $currency ? strtoupper($currency) : self::getCurrency();
        return $currency === self::BDT ? self::BDT_RATE : self::USD_RATE;
    }

    /**
     * Get currency symbol.
     */
    public static function getSymbol(string $currency = null): string
    {
        $currency = $currency ? strtoupper($currency) : self::getCurrency();
        return $currency === self::BDT ? 'BDT' : '$';
    }

    /**
     * Convert an amount from USD to target currency.
     */
    public static function convertFromUsd(float $usdAmount, string $targetCurrency = null): float
    {
        $targetCurrency = $targetCurrency ? strtoupper($targetCurrency) : self::getCurrency();
        if ($targetCurrency === self::BDT) {
            return $usdAmount * self::BDT_RATE;
        }
        return $usdAmount;
    }

    /**
     * Convert an amount from BDT to USD.
     */
    public static function convertToUsd(float $bdtAmount): float
    {
        return $bdtAmount / self::BDT_RATE;
    }

    /**
     * Format a USD amount into active or specific currency string.
     */
    public static function format(float $usdAmount, string $targetCurrency = null, bool $includeSymbol = true): string
    {
        $targetCurrency = $targetCurrency ? strtoupper($targetCurrency) : self::getCurrency();
        $symbol = self::getSymbol($targetCurrency);
        $converted = self::convertFromUsd($usdAmount, $targetCurrency);

        $formattedNumber = number_format($converted, ($converted == (int)$converted ? 0 : 2));

        if (!$includeSymbol) {
            return $formattedNumber;
        }

        return $targetCurrency === self::BDT
            ? "{$formattedNumber} BDT"
            : "{$symbol}{$formattedNumber}";
    }
}
