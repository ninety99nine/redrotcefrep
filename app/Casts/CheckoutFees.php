<?php

namespace App\Casts;

use stdClass;
use App\Services\MoneyService;
use App\Services\PercentageService;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class CheckoutFees implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return stdClass
     */
    public function get($model, $key, $value, $attributes)
    {
        if (is_null($value)) {
            return [];
        } elseif (is_string($value)) {
            $value = json_decode($value, true);
        }

        foreach ($value as $key => $checkoutFee) {
            $value[$key]['flat_rate'] = MoneyService::convertToMoneyFormat($checkoutFee['flat_rate'], $attributes['currency']);
            $value[$key]['percentage_rate'] = PercentageService::convertToPercentageFormat($checkoutFee['percentage_rate']);
        }

        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string|null
     */
    public function set($model, $key, $value, $attributes): ?string
    {
        if (is_null($value) || empty($value)) {
            return null;
        }

        if (!is_array($value)) {
            throw new \InvalidArgumentException('Checkout fees must be an array.');
        }

        $currency = $attributes['currency'] ?? 'USD';
        $currencyDetails = MoneyService::findCurrencyByCode($currency);
        $decimalDigits = $currencyDetails['decimal_digits'] ?? 2;

        $normalizedFees = array_map(function ($fee) use ($decimalDigits) {

            // Normalize flat_rate
            $flatRate = $fee['flat_rate'] ?? 0;

            if ($flatRate instanceof stdClass && isset($flatRate->amount)) {
                $flatRate = $flatRate->amount;
            } elseif (is_array($flatRate) && isset($flatRate['amount'])) {
                $flatRate = $flatRate['amount'];
            }
            $flatRate = (float) $flatRate;
            $flatRate = number_format($flatRate, $decimalDigits, '.', '');

            // Normalize percentage_rate
            $percentageRate = $fee['percentage_rate'] ?? 0;
            if (is_array($percentageRate) && isset($percentageRate['value'])) {
                $percentageRate = $percentageRate['value'];
            }
            $percentageRate = PercentageService::normalizePercentageValue($percentageRate);

            return [
                'name' => $fee['name'] ?? 'Fee',
                'flat_rate' => $flatRate,
                'percentage_rate' => $percentageRate,
            ];
        }, $value);

        return json_encode($normalizedFees);
    }
}
