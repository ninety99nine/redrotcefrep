<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderFeeResources extends ResourceCollection
{
    public $collects = OrderFeeResource::class;
}
