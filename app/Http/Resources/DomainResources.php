<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DomainResources extends ResourceCollection
{
    public $collects = DomainResource::class;
}
