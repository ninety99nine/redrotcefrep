<?php

namespace App\Services;

class PercentageService
{
    /**
     * Convert a percentage value to a formatted array.
     *
     * @param mixed $value The percentage value (e.g., 5, '5', '5%')
     * @return array An array with 'value' (float) and 'value_symbol' (string with %)
     */
    public static function convertToPercentageFormat($value): array
    {
        // Normalize input to float
        $roundedValue = self::normalizePercentageValue($value);

        return [
            'value' => $roundedValue,
            'value_symbol' => $roundedValue . '%',
        ];
    }

    /**
     * Normalize a percentage value to a float.
     *
     * @param mixed $value The input value (e.g., 5, '5', '5%')
     * @return float The normalized percentage value
     */
    public static function normalizePercentageValue($value): float
    {
        if (is_null($value)) {
            return 0.0;
        }

        // Handle string inputs like '5%' or '5.00'
        if (is_string($value)) {
            $value = str_replace('%', '', $value);
            $value = trim($value);
        }

        // Convert to float, default to 0 if invalid
        return (float) ($value ?? 0);
    }
}
