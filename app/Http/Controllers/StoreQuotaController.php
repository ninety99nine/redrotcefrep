<?php

namespace App\Http\Controllers;

use App\Models\StoreQuota;
use App\Services\StoreQuotaService;
use App\Http\Resources\StoreQuotaResource;
use App\Http\Resources\StoreQuotaResources;
use App\Http\Requests\StoreQuota\ShowStoreQuotaRequest;
use App\Http\Requests\StoreQuota\ShowStoreQuotasRequest;
use App\Http\Requests\StoreQuota\CreateStoreQuotaRequest;
use App\Http\Requests\StoreQuota\UpdateStoreQuotaRequest;
use App\Http\Requests\StoreQuota\DeleteStoreQuotaRequest;
use App\Http\Requests\StoreQuota\DeleteStoreQuotasRequest;

class StoreQuotaController extends Controller
{
    /**
     * @var StoreQuotaService
     */
    protected $service;

    /**
     * StoreQuotaController constructor.
     *
     * @param StoreQuotaService $service
     */
    public function __construct(StoreQuotaService $service)
    {
        $this->service = $service;
    }

    /**
     * Show store quotas.
     *
     * @param ShowStoreQuotasRequest $request
     * @return StoreQuotaResources|array
     */
    public function showStoreQuotas(ShowStoreQuotasRequest $request): StoreQuotaResources|array
    {
        return $this->service->showStoreQuotas($request->validated());
    }

    /**
     * Create store quota.
     *
     * @param CreateStoreQuotaRequest $request
     * @return array
     */
    public function createStoreQuota(CreateStoreQuotaRequest $request): array
    {
        return $this->service->createStoreQuota($request->validated());
    }

    /**
     * Delete multiple store quotas.
     *
     * @param DeleteStoreQuotasRequest $request
     * @return array
     */
    public function deleteStoreQuotas(DeleteStoreQuotasRequest $request): array
    {
        $storeQuotaIds = request()->input('store_quota_ids', []);
        return $this->service->deleteStoreQuotas($storeQuotaIds);
    }

    /**
     * Show single store quota.
     *
     * @param ShowStoreQuotaRequest $request
     * @param StoreQuota $storeQuota
     * @return StoreQuotaResource
     */
    public function showStoreQuota(ShowStoreQuotaRequest $request, StoreQuota $storeQuota): StoreQuotaResource
    {
        return $this->service->showStoreQuota($storeQuota);
    }

    /**
     * Update store quota.
     *
     * @param UpdateStoreQuotaRequest $request
     * @param StoreQuota $storeQuota
     * @return array
     */
    public function updateStoreQuota(UpdateStoreQuotaRequest $request, StoreQuota $storeQuota): array
    {
        return $this->service->updateStoreQuota($storeQuota, $request->validated());
    }

    /**
     * Delete store quota.
     *
     * @param DeleteStoreQuotaRequest $request
     * @param StoreQuota $storeQuota
     * @return array
     */
    public function deleteStoreQuota(DeleteStoreQuotaRequest $request, StoreQuota $storeQuota): array
    {
        return $this->service->deleteStoreQuota($storeQuota);
    }
}
