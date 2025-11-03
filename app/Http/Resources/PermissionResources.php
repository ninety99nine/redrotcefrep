<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PermissionResources extends ResourceCollection
{
    public $collects = PermissionResource::class;
}
