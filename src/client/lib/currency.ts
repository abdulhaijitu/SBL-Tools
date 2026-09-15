import { useState, useEffect } from "react";

export type CurrencyType = "USD" | "BDT";

// Central exchange rate configuration: 1 USD = 100 BDT (SBL Ecosystem)
export const BDT_PER_USD = 100;

const STORAGE_KEY = "sbl_selected_currency";

export function getStoredCurrency(): CurrencyType {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved === "BDT" || saved === "USD") {
            return saved;
        }
    } catch {
        // Fallback to default
    }
    // Default is strictly USD as requested
    return "USD";
}

export function setStoredCurrency(currency: CurrencyType): void {
    try {
        localStorage.setItem(STORAGE_KEY, currency);
    } catch {
        // LocalStorage may fail in private mode
    }
    window.dispatchEvent(
        new CustomEvent("currency:change", { detail: currency }),
    );
}

/**
 * Global reactive hook to track active currency
 */
export function useCurrency(): [CurrencyType, (c: CurrencyType) => void] {
    const [currency, setCurrencyState] =
        useState<CurrencyType>(getStoredCurrency);

    useEffect(() => {
        const handler = (e: Event) => {
            const customEvent = e as CustomEvent<CurrencyType>;
            setCurrencyState(customEvent.detail || getStoredCurrency());
        };

        window.addEventListener("currency:change", handler);
        return () => window.removeEventListener("currency:change", handler);
    }, []);

    const setCurrency = (c: CurrencyType) => {
        setCurrencyState(c);
        setStoredCurrency(c);
    };

    return [currency, setCurrency];
}

/**
 * Universal Currency Formatter
 * Takes an amount in base BDT and formats it into the requested or stored currency
 */
export function formatMoney(
    amountInBdt: number | string | null | undefined,
    currency?: CurrencyType,
    options?: { showDecimals?: boolean; compact?: boolean },
): string {
    const activeCurrency = currency || getStoredCurrency();
    const numericBdt =
        typeof amountInBdt === "string"
            ? parseFloat(amountInBdt) || 0
            : amountInBdt || 0;

    if (activeCurrency === "USD") {
        const usdValue = numericBdt / BDT_PER_USD;
        // Format USD: $1,250 or $1,250.50
        const formatted = new Intl.NumberFormat("en-US", {
            style: "currency",
            currency: "USD",
            minimumFractionDigits: options?.showDecimals
                ? 2
                : usdValue % 1 === 0
                  ? 0
                  : 2,
            maximumFractionDigits: 2,
        }).format(usdValue);
        return formatted;
    } else {
        // Format BDT: ৳1,50,000
        const formattedNumber = new Intl.NumberFormat("en-IN", {
            maximumFractionDigits: options?.showDecimals ? 2 : 0,
        }).format(Math.round(numericBdt));
        return `৳${formattedNumber}`;
    }
}
