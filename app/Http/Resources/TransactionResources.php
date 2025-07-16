<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionResources extends ResourceCollection
{
    public $collects = TransactionResource::class;
}
