<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderDiscountResources extends ResourceCollection
{
    public $collects = OrderDiscountResource::class;
}
