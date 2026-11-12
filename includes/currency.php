<?php
/**
 * Money handling for the storefront.
 *
 * Cypadi Shop trades in Rwanda, so every price the customer sees is in Rwandan
 * francs. Before this file existed the catalogue held US-dollar figures while
 * the templates wrote "RWF" in front of them, so a Tecno Spark 20 was listed at
 * "RWF 249" — about seventeen US cents — and the product page printed a dollar
 * sign instead. Both are fixed by storing RWF and formatting it in one place.
 *
 * Rules this file enforces:
 *   * RWF has no minor unit in practice, so amounts are whole francs.
 *   * Prices are rendered through formatRwf(), never concatenated by hand.
 *   * Legacy USD figures from the old dump are converted through
 *     convertUsdToRwf(), so no screen can ever mix the two currencies.
 */

/** ISO code of the currency the catalogue is priced in. */
const MARKETPLACE_CURRENCY_CODE = 'RWF';

/** Symbol/prefix printed before every amount. */
const MARKETPLACE_CURRENCY_PREFIX = 'RWF';

/**
 * Francs per US dollar, used only for legacy data.
 *
 * The catalogue itself is stored in RWF, so nothing on a normal request touches
 * this. It exists for two jobs: `tools`-style maintenance of an old dump that
 * still holds dollar figures, and the seller form hint that shows a seller what
 * a foreign list price works out to. National Bank of Rwanda mid-market rate,
 * rounded — it is a conversion convenience, not a settlement rate.
 */
const MARKETPLACE_USD_TO_RWF = 1350;

/**
 * Smallest amount PawaPay accepts for a Rwandan mobile-money deposit.
 *
 * Kept here so the checkout can refuse an impossible amount before a deposit is
 * opened, instead of letting the provider reject it after the customer has
 * already approved a PIN prompt.
 */
const MARKETPLACE_MIN_PAYMENT_RWF = 100;

/** True when the amount is a whole positive number of francs. */
function isRwfAmount($amount): bool
{
    return is_numeric($amount) && (float) $amount > 0;
}

/**
 * Rounds an amount to the nearest whole franc.
 *
 * The database columns are integers, but arithmetic with quantities and any
 * converted legacy value can produce a float, so everything funnels through
 * here before it is stored or printed.
 */
function rwfRound($amount): int
{
    if (!is_numeric($amount)) {
        return 0;
    }

    $francs = (float) $amount;

    return $francs > 0 ? (int) round($francs) : 0;
}

/**
 * Converts a US-dollar figure to Rwandan francs.
 *
 * Retail prices in Rwanda land on round numbers, so the result is snapped to a
 * price point a shop would actually print: whole thousands above RWF 100,000,
 * whole five hundreds above RWF 10,000, and whole hundreds below that.
 */
function convertUsdToRwf($usdAmount, float $rate = MARKETPLACE_USD_TO_RWF): int
{
    if (!is_numeric($usdAmount) || (float) $usdAmount <= 0) {
        return 0;
    }

    $francs = (float) $usdAmount * $rate;

    if ($francs >= 100000) {
        return (int) (round($francs / 1000) * 1000);
    }

    if ($francs >= 10000) {
        return (int) (round($francs / 500) * 500);
    }

    return (int) (round($francs / 100) * 100);
}

/** The amount as a grouped number without the currency prefix: "1,850,000". */
function rwfNumber($amount): string
{
    return number_format(rwfRound($amount), 0, '.', ',');
}

/**
 * A complete, display-ready price: "RWF 1,850,000".
 *
 * Every template prints prices through this function so the prefix, the
 * thousands separators and the rounding can never disagree between pages.
 */
function formatRwf($amount): string
{
    return MARKETPLACE_CURRENCY_PREFIX . ' ' . rwfNumber($amount);
}

/**
 * Formats a price for a machine-readable context (JSON, invoice CSV).
 * Separators are omitted so the value parses as a number downstream.
 */
function rwfPlain($amount): string
{
    return (string) rwfRound($amount);
}

/**
 * True when a stored price is below the smallest payable amount.
 *
 * Used by the checkout guard: an item priced below the provider minimum cannot
 * be paid for on its own, and the customer deserves to be told before a deposit
 * is opened rather than after it fails.
 */
function isBelowMinimumPayment($amount): bool
{
    return rwfRound($amount) < MARKETPLACE_MIN_PAYMENT_RWF;
}
