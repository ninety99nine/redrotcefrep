<?php

namespace App\Casts;

use stdClass;
use App\Services\MoneyService;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Money implements CastsAttributes
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
    public function get($model, string $key, $value, array $attributes): stdClass
    {
        return MoneyService::convertToMoneyFormat($value, $attributes['currency']);
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
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        } elseif ($value instanceof stdClass && isset($value->amount)) {
            return (float) $value->amount;
        } elseif (is_array($value) && isset($value['amount'])) {
            return (float) $value['amount'];
        } else {
            return (float) $value;
        }
    }
}
