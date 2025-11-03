<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class StoreUserResources extends ResourceCollection
{
    public $collects = StoreUserResource::class;
}
