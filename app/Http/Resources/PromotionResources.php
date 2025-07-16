<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PromotionResources extends ResourceCollection
{
    public $collects = PromotionResource::class;
}
