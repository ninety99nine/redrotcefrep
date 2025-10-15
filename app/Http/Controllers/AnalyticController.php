<?php

namespace App\Http\Controllers;

use App\Services\AnalyticService;
use App\Http\Requests\Analytic\ShowAnalyticsRequest;

class AnalyticController extends Controller
{
    /**
     * @var AnalyticService
     */
    protected $service;

    /**
     * AnalyticController constructor.
     *
     * @param AnalyticService $service
     */
    public function __construct(AnalyticService $service)
    {
        $this->service = $service;
    }

    /**
     * Show analytics.
     *
     * @param ShowAnalyticsRequest $request
     * @return array
     */
    public function showAnalytics(ShowAnalyticsRequest $request): array
    {
        return $this->service->showAnalytics($request->validated());
    }
}
