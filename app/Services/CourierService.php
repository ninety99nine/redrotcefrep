<?php

namespace App\Services;

use Exception;
use App\Models\Courier;
use App\Http\Resources\CourierResource;
use App\Http\Resources\CourierResources;

class CourierService extends BaseService
{
    /**
     * Show couriers.
     *
     * @param array $data
     * @return CourierResources|array
     */
    public function showCouriers(array $data): CourierResources|array
    {
        $query = Courier::query()->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create courier.
     *
     * @param array $data
     * @return array
     */
    public function createCourier(array $data): array
    {
        $courier = Courier::create($data);
        return $this->showCreatedResource($courier);
    }

    /**
     * Show courier by code.
     *
     * @param string $code
     * @return CourierResource
     */
    public function showCourierByCode(string $code): CourierResource
    {
        $courier = Courier::where('id', $code)->orWhere('code', $code)
                    ->with($this->getRequestRelationships())
                    ->withCount($this->getRequestCountableRelationships())
                    ->firstOrFail();

        return $this->showResource($courier);
    }

    /**
     * Delete Couriers.
     *
     * @param array $courierIds
     * @return array
     * @throws Exception
     */
    public function deleteCouriers(array $courierIds): array
    {
        $couriers = Courier::whereIn('id', $courierIds)->get();

        if ($totalCouriers = $couriers->count()) {

            foreach ($couriers as $courier) {

                $this->deleteCourier($courier);

            }

            return ['message' => $totalCouriers . ($totalCouriers == 1 ? ' Courier' : ' Couriers') . ' deleted'];

        } else {
            throw new Exception('No Couriers deleted');
        }
    }

    /**
     * Show courier.
     *
     * @param Courier $courier
     * @return CourierResource
     */
    public function showCourier(Courier $courier): CourierResource
    {
        return $this->showResource($courier);
    }

    /**
     * Update courier.
     *
     * @param Courier $courier
     * @param array $data
     * @return array
     */
    public function updateCourier(Courier $courier, array $data): array
    {
        $courier->update($data);
        return $this->showUpdatedResource($courier);
    }

    /**
     * Delete courier.
     *
     * @param Courier $courier
     * @return array
     * @throws Exception
     */
    public function deleteCourier(Courier $courier): array
    {
        $deleted = $courier->delete();

        if ($deleted) {
            return ['message' => 'Courier deleted'];
        } else {
            throw new Exception('Courier delete unsuccessful');
        }
    }
}
