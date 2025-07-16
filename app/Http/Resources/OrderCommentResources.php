<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderCommentResources extends ResourceCollection
{
    public $collects = OrderCommentResource::class;
}
