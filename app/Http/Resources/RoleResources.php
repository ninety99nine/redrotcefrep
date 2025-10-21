<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RolesResources extends ResourceCollection
{
    public $collects = RoleResource::class;
}
