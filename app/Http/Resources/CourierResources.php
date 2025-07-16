<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CourierResources extends ResourceCollection
{
    public $collects = CourierResource::class;
}
