<?php

namespace App\Http\Controllers;

use App\Models\Courier;
use App\Services\CourierService;
use App\Http\Resources\CourierResource;
use App\Http\Resources\CourierResources;
use App\Http\Requests\Courier\ShowCourierRequest;
use App\Http\Requests\Courier\ShowCouriersRequest;
use App\Http\Requests\Courier\CreateCourierRequest;
use App\Http\Requests\Courier\UpdateCourierRequest;
use App\Http\Requests\Courier\DeleteCourierRequest;
use App\Http\Requests\Courier\DeleteCouriersRequest;

class CourierController extends Controller
{
    /**
     * @var CourierService
     */
    protected $service;

    /**
     * CourierController constructor.
     *
     * @param CourierService $service
     */
    public function __construct(CourierService $service)
    {
        $this->service = $service;
    }

    /**
     * Show couriers.
     *
     * @param ShowCouriersRequest $request
     * @return CourierResources|array
     */
    public function showCouriers(ShowCouriersRequest $request): CourierResources|array
    {
        return $this->service->showCouriers($request->validated());
    }

    /**
     * Create courier.
     *
     * @param CreateCourierRequest $request
     * @return array
     */
    public function createCourier(CreateCourierRequest $request): array
    {
        return $this->service->createCourier($request->validated());
    }

    /**
     * Delete multiple couriers.
     *
     * @param DeleteCouriersRequest $request
     * @return array
     */
    public function deleteCouriers(DeleteCouriersRequest $request): array
    {
        $courierIds = request()->input('courier_ids', []);
        return $this->service->deleteCouriers($courierIds);
    }

    /**
     * Show single courier.
     *
     * @param ShowCourierRequest $request
     * @param Courier $courier
     * @return CourierResource
     */
    public function showCourier(ShowCourierRequest $request, Courier $courier): CourierResource
    {
        return $this->service->showCourier($courier);
    }

    /**
     * Update courier.
     *
     * @param UpdateCourierRequest $request
     * @param Courier $courier
     * @return array
     */
    public function updateCourier(UpdateCourierRequest $request, Courier $courier): array
    {
        return $this->service->updateCourier($courier, $request->validated());
    }

    /**
     * Delete courier.
     *
     * @param DeleteCourierRequest $request
     * @param Courier $courier
     * @return array
     */
    public function deleteCourier(DeleteCourierRequest $request, Courier $courier): array
    {
        return $this->service->deleteCourier($courier);
    }
}
