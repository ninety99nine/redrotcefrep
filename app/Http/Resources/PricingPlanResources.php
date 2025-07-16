<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PricingPlanResources extends ResourceCollection
{
    public $collects = PricingPlanResource::class;
}
