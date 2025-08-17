<?php

namespace App\Http\Controllers;

use App\Services\UssdService;

class UssdController extends Controller
{
    /**
     * @var UssdService
     */
    protected $service;

    /**
     * UssdController constructor.
     *
     * @param UssdService $service
     */
    public function __construct(UssdService $service)
    {
        $this->service = $service;
    }

    /**
     * Show profile summary.
     *
     * @return array
     */
    public function showProfileSummary(): array
    {
        return $this->service->showProfileSummary();
    }
}
