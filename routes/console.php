<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

Artisan::command('log:test', function () {
    Log::info('Hello from log:test');
});
