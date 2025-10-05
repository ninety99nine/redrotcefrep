<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Services\DomainService;
use App\Http\Resources\DomainResource;
use App\Http\Resources\DomainResources;
use App\Http\Requests\Domain\ShowDomainRequest;
use App\Http\Requests\Domain\ShowDomainsRequest;
use App\Http\Requests\Domain\CreateDomainRequest;
use App\Http\Requests\Domain\UpdateDomainRequest;
use App\Http\Requests\Domain\DeleteDomainRequest;
use App\Http\Requests\Domain\DeleteDomainsRequest;
use App\Http\Requests\Domain\PurchaseDomainRequest;
use App\Http\Requests\Domain\SearchDomainsRequest;
use App\Http\Requests\Domain\ShowDomainPricingRequest;
use App\Http\Requests\Domain\VerifyDomainConnectionRequest;

class DomainController extends Controller
{
    /**
     * @var DomainService
     */
    protected $service;

    /**
     * DomainController constructor.
     *
     * @param DomainService $service
     */
    public function __construct(DomainService $service)
    {
        $this->service = $service;
    }

    /**
     * Show domains.
     *
     * @param ShowDomainsRequest $request
     * @return DomainResources|array
     */
    public function showDomains(ShowDomainsRequest $request): DomainResources|array
    {
        return $this->service->showDomains($request->validated());
    }

    /**
     * Create domain.
     *
     * @param CreateDomainRequest $request
     * @return array
     */
    public function createDomain(CreateDomainRequest $request): array
    {
        return $this->service->createDomain($request->validated());
    }

    /**
     * Delete multiple domains.
     *
     * @param DeleteDomainsRequest $request
     * @return array
     */
    public function deleteDomains(DeleteDomainsRequest $request): array
    {
        $domainIds = request()->input('domain_ids', []);
        return $this->service->deleteDomains($domainIds);
    }

    /**
     * Show server IP.
     *
     * @return array
     */
    public function showServerIp(): array
    {
        return $this->service->showServerIp();
    }

    /**
     * Show domain pricing.
     *
     * @param ShowDomainPricingRequest $request
     * @return array
     */
    public function showDomainPricing(ShowDomainPricingRequest $request): array
    {
        return $this->service->showDomainPricing($request->input('tld'));
    }

    /**
     * Search domains.
     *
     * @param SearchDomainsRequest $request
     * @return array
     */
    public function searchDomains(SearchDomainsRequest $request): array
    {
        return $this->service->searchDomains($request->validated());
    }

    /**
     * Purchase domain.
     *
     * @param PurchaseDomainRequest $request
     * @return array
     */
    public function purchaseDomain(PurchaseDomainRequest $request): array
    {
        return $this->service->purchaseDomain($request->validated());
    }

    /**
     * Verify domain connection.
     *
     * @param VerifyDomainConnectionRequest $request
     * @param Domain $domain
     * @return array
     */
    public function verifyDomainConnection(VerifyDomainConnectionRequest $request): array
    {
        return $this->service->verifyDomainConnection($request->validated());
    }

    /**
     * Show domain.
     *
     * @param ShowDomainRequest $request
     * @param Domain $domain
     * @return DomainResource
     */
    public function showDomain(ShowDomainRequest $request, Domain $domain): DomainResource
    {
        return $this->service->showDomain($domain);
    }

    /**
     * Update domain.
     *
     * @param UpdateDomainRequest $request
     * @param Domain $domain
     * @return array
     */
    public function updateDomain(UpdateDomainRequest $request, Domain $domain): array
    {
        return $this->service->updateDomain($domain, $request->validated());
    }

    /**
     * Delete domain.
     *
     * @param DeleteDomainRequest $request
     * @param Domain $domain
     * @return array
     */
    public function deleteDomain(DeleteDomainRequest $request, Domain $domain): array
    {
        return $this->service->deleteDomain($domain);
    }
}
