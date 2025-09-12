import currencies from '@Json/currencies.json'

export function generateRandomNumber(length) {
    return Array.from({ length }, () => Math.floor(Math.random() * 10)).join('');
}

export function removeDecimalTrailingZeros(value) {
    const number = parseFloat(value);
    return number.toFixed(2).replace(/\.?0+$/, '');
}

/**
 * Convert a value to a monetary format based on the currency's decimal digits.
 *
 * @param {number|string} value - The numeric value to format.
 * @param {string} currency - The currency code (e.g., 'USD', 'CAD').
 * @param {boolean} allowNegativeAmounts - Whether to allow negative values.
 * @returns {string} - The formatted money string without the currency symbol.
 */
export function convertToMoney(value, currency, allowNegativeAmounts = false) {
    let numericValue = value.toString().replace(/[^0-9.-]/g, '');

    numericValue = parseFloat(numericValue) || 0;
    const decimalPlaces = currencies[currency]?.decimal_digits ?? 2;

    if (!allowNegativeAmounts) {
        numericValue = Math.max(0, numericValue);
    }

    return numericValue.toFixed(decimalPlaces);
}

/**
 * Convert a value to a monetary format with the currency symbol.
 *
 * @param {number|string} value - The numeric value to format.
 * @param {string} currency - The currency code (e.g., 'USD', 'CAD').
 * @param {boolean} allowNegativeAmounts - Whether to allow negative values.
 * @returns {string} - The formatted money string with the currency symbol (e.g., '$100.00' or '100.00 €').
 */
export function convertToMoneyWithSymbol(value, currency, allowNegativeAmounts = false) {
    let numericValue = value.toString().replace(/[^0-9.-]/g, '');

    numericValue = parseFloat(numericValue) || 0;
    const decimalPlaces = currencies[currency]?.decimal_digits ?? 2;
    const symbol = currencies[currency]?.symbol_native ?? currency;

    if (!allowNegativeAmounts) {
        numericValue = Math.max(0, numericValue);
    }

    const formattedValue = numericValue.toFixed(decimalPlaces);

    // Determine symbol placement (before or after) based on currency conventions
    const symbolAfter = ['EUR', 'CAD', 'AUD', 'BRL', 'CZK', 'DKK', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'THB', 'TRY'];
    if (symbolAfter.includes(currency)) {
        return `${formattedValue} ${symbol}`;
    }

    return `${symbol}${formattedValue}`;
}
