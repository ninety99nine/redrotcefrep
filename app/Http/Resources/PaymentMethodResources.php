<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PaymentMethodResources extends ResourceCollection
{
    public $collects = PaymentMethodResource::class;
}
