<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DeliveryMethodResources extends ResourceCollection
{
    public $collects = DeliveryMethodResource::class;
}
