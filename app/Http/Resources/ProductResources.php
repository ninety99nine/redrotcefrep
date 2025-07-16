<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductResources extends ResourceCollection
{
    public $collects = ProductResource::class;
}
