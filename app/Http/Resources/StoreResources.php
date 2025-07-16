<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class StoreResources extends ResourceCollection
{
    public $collects = StoreResource::class;
}
