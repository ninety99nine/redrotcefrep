<?php

namespace App\Services;

use Exception;
use App\Models\Domain;
use App\Enums\DomainStatus;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\DomainResource;
use App\Http\Resources\DomainResources;

class DomainService extends BaseService
{
    /**
     * Show domains.
     *
     * @param array $data
     * @return DomainResources|array
     */
    public function showDomains(array $data): DomainResources|array
    {
        $storeId = $data['store_id'] ?? null;

        $query = Domain::query();

        if ($storeId) {
            $query = $query->where('store_id', $storeId);
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create domain.
     *
     * @param array $data
     * @return array
     */
    public function createDomain(array $data): array
    {
        $domain = Domain::create($data);
        $verifiedDomain = $this->verifyConnection($domain);
        return $this->showCreatedResource($verifiedDomain);
    }

    /**
     * Delete domains.
     *
     * @param array $domainIds
     * @return array
     * @throws Exception
     */
    public function deleteDomains(array $domainIds): array
    {
        $domains = Domain::whereIn('id', $domainIds)->get();

        if ($totalDomains = $domains->count()) {
            foreach ($domains as $domain) {
                $this->deleteDomain($domain);
            }

            return ['message' => $totalDomains . ($totalDomains == 1 ? ' Domain' : ' Domains') . ' deleted'];
        } else {
            throw new Exception('No Domains deleted');
        }
    }

    /**
     * Show server IP.
     *
     * @return array
     */
    public function showServerIp(): array
    {
        return ['server_ip' => $this->resolveServerIp()];
    }

    /**
     * Verify domain connection.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function verifyDomainConnection(array $data): array
    {
        $name = $data['name'];
        $connected = $this->verifyConnection($name);

        return [
            'connected' => $connected
        ];
    }

    /**
     * Show domain.
     *
     * @param Domain $domain
     * @return DomainResource
     */
    public function showDomain(Domain $domain): DomainResource
    {
        return $this->showResource($domain);
    }

    /**
     * Update domain.
     *
     * @param Domain $domain
     * @param array $data
     * @return array
     */
    public function updateDomain(Domain $domain, array $data): array
    {
        $domain->update($data);
        $verifiedDomain = $this->verifyConnection($domain);
        return $this->showUpdatedResource($verifiedDomain);
    }

    /**
     * Delete domain.
     *
     * @param Domain $domain
     * @return array
     * @throws Exception
     */
    public function deleteDomain(Domain $domain): array
    {
        $deleted = $domain->delete();

        if ($deleted) {
            return ['message' => 'Domain deleted'];
        } else {
            throw new Exception('Domain delete unsuccessful');
        }
    }

    /**
     * Verify connection.
     *
     * @param string|Domain $name
     * @return bool|Domain
     */
    private function verifyConnection(string|Domain $domain): bool|Domain
    {
        $name = $domain instanceof Domain ? $domain->name : $domain;
        $serverIp = $this->resolveServerIp();
        $resolvedIp = gethostbyname($name);

        $connected = $resolvedIp === $serverIp;

        if($domain instanceof Domain) {

            if ($connected) {
                $domain->update([
                    'status' => DomainStatus::CONNECTED->value,
                    'verified_at' => now()
                ]);
            } else {
                $domain->update(['status' => DomainStatus::PENDING->value]);
            }

            return $domain;

        }else{

            return $connected;

        }
    }

    /**
     * Resolve server IP from APP_URL.
     *
     * @return string
     * @throws Exception
     */
    private function resolveServerIp(): string
    {
        $appUrl = config('app.url');
        $hostname = parse_url($appUrl, PHP_URL_HOST);

        if (!$hostname) {
            throw new Exception('Unable to parse hostname from APP_URL: ' . $appUrl);
        }

        return Cache::remember('app_server_ip', now()->addHours(24), function () use ($hostname) {
            $ip = gethostbyname($hostname);
            if ($ip === $hostname) {
                throw new Exception('Unable to resolve IP for ' . $hostname);
            }
            return $ip;
        });
    }
}
