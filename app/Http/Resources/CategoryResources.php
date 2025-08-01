<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryResources extends ResourceCollection
{
    public $collects = CategoryResource::class;
}
