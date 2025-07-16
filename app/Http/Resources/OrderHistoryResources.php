<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderHistoryResources extends ResourceCollection
{
    public $collects = OrderHistoryResource::class;
}
