<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReviewResources extends ResourceCollection
{
    public $collects = ReviewResource::class;
}
