<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WorkflowResources extends ResourceCollection
{
    public $collects = WorkflowResource::class;
}
