<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class StorePaymentMethodResources extends ResourceCollection
{
    public $collects = StorePaymentMethodResource::class;
}
