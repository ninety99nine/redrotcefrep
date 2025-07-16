<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AddressResources extends ResourceCollection
{
    public $collects = AddressResource::class;
}
