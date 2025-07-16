<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderProductResources extends ResourceCollection
{
    public $collects = OrderProductResource::class;
}
