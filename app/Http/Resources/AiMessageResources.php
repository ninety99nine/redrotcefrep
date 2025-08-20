<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AiMessageResources extends ResourceCollection
{
    public $collects = AiMessageResource::class;
}
