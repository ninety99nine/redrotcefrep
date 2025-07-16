<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SubscriptionResources extends ResourceCollection
{
    public $collects = SubscriptionResource::class;
}
