<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerResources extends ResourceCollection
{
    public $collects = CustomerResource::class;
}
