<?php

namespace App\Observers;

use App\Models\DeliveryMethod;
use App\Enums\DeliveryMethodFeeType;

class DeliveryMethodObserver
{
    public function saving(DeliveryMethod $deliveryMethod): DeliveryMethod
    {
        if (isset($deliveryMethod->fee_type) && in_array($deliveryMethod->fee_type, [
            DeliveryMethodFeeType::FEE_BY_DISTANCE->value,
            DeliveryMethodFeeType::FEE_BY_POSTAL_CODE->value,
        ])) {
            $deliveryMethod->ask_for_an_address = true;
        }

        if (isset($deliveryMethod->ask_for_an_address) && !$deliveryMethod->ask_for_an_address) {
            $deliveryMethod->pin_location_on_map = false;
        }

        return $deliveryMethod;
    }

    public function creating(DeliveryMethod $deliveryMethod): void
    {
        //
    }

    public function created(DeliveryMethod $deliveryMethod): void
    {
        //
    }

    public function updated(DeliveryMethod $deliveryMethod): void
    {
        //
    }

    public function deleting(DeliveryMethod $deliveryMethod): void
    {
        //
    }

    public function deleted(DeliveryMethod $deliveryMethod): void
    {
        //
    }

    public function restored(DeliveryMethod $deliveryMethod): void
    {
        //
    }

    public function forceDeleted(DeliveryMethod $deliveryMethod): void
    {
        //
    }
}
