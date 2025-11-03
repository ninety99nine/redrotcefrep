<?php

namespace App\Services;

use Exception;
use App\Models\StoreQuota;
use App\Http\Resources\StoreQuotaResource;
use App\Http\Resources\StoreQuotaResources;

class StoreQuotaService extends BaseService
{
    /**
     * Show store quotas.
     *
     * @param array $data
     * @return StoreQuotaResources|array
     */
    public function showStoreQuotas(array $data): StoreQuotaResources|array
    {
        $query = StoreQuota::query()->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create store quota.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createStoreQuota(array $data): array
    {
        $storeQuota = StoreQuota::create($data);
        return $this->showCreatedResource($storeQuota);
    }

    /**
     * Delete store quotas.
     *
     * @param array $storeQuotaIds
     * @return array
     * @throws Exception
     */
    public function deleteStoreQuotas(array $storeQuotaIds): array
    {
        $storeQuotas = StoreQuota::whereIn('id', $storeQuotaIds)->get();

        if ($totalStoreQuotas = $storeQuotas->count()) {

            foreach ($storeQuotas as $storeQuota) {

                $this->deleteStoreQuota($storeQuota);

            }

            return ['message' => $totalStoreQuotas . ($totalStoreQuotas == 1 ? ' Store quota' : ' Store quotas') . ' deleted'];

        } else {
            throw new Exception('No Store quotas deleted');
        }
    }

    /**
     * Show store quota.
     *
     * @param StoreQuota $storeQuota
     * @return StoreQuotaResource
     */
    public function showStoreQuota(StoreQuota $storeQuota): StoreQuotaResource
    {
        return $this->showResource($storeQuota);
    }

    /**
     * Update store quota.
     *
     * @param StoreQuota $storeQuota
     * @param array $data
     * @return array
     */
    public function updateStoreQuota(StoreQuota $storeQuota, array $data): array
    {
        $storeQuota->update($data);
        return $this->showUpdatedResource($storeQuota);
    }

    /**
     * Delete store quota.
     *
     * @param StoreQuota $storeQuota
     * @return array
     * @throws Exception
     */
    public function deleteStoreQuota(StoreQuota $storeQuota): array
    {
        $deleted = $storeQuota->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Store quota deleted' : 'Store quota delete unsuccessful'
        ];
    }
}
