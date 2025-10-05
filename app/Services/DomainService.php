<?php

namespace App\Services;

use Exception;
use App\Models\Store;
use App\Models\Domain;
use App\Enums\DomainStatus;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Enums\PaymentMethodType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\DomainResource;
use App\Http\Resources\DomainResources;
use App\Enums\TransactionPaymentStatus;
use App\Enums\TransactionVerificationType;
use App\Http\Resources\TransactionResource;

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
     * Show domain pricing for a specific TLD.
     *
     * @param string $tld
     * @return array
     */
    public function showDomainPricing(string $tld): array
    {
        return NamecheapService::getDomainPricing($tld);
    }

    /**
     * Search domains.
     *
     * @param array $data
     * @return array
     */
    public function searchDomains(array $data): array
    {
        try {
            $searchTerm = $data['search'];
            $results = NamecheapService::searchDomains($searchTerm);

            return [
                'successful' => true,
                'domains' => $results
            ];
        } catch (Exception $e) {
            return [
                'successful' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Purchase domain.
     *
     * @param array $data
     * @return TransactionResource|array
     * @throws Exception
     */
    public function purchaseDomain(array $data): TransactionResource|array
    {
        try {
            $user = Auth::user();
            $store = Store::find($data['store_id']);

            if (!$store) {
                throw new Exception('The store does not exist');
            }

            $paymentMethod = PaymentMethod::whereType(PaymentMethodType::DPO->value)->first();

            if (!$paymentMethod || !$paymentMethod->active) {
                throw new Exception('The DPO payment method is not available');
            }

            $name = $data['domain_name'];
            $price = $data['domain_price'];

            if ($store->domains()->where('name', $name)->exists()) {
                throw new Exception('This domain already exists');
            }

            $domain = $store->domains()->create(['name' => $name]);

            $transactionPayload = $this->prepareTransactionPayload($user, $store, $domain, $price, $paymentMethod);
            $transaction = Transaction::create($transactionPayload);

            $transaction->setRelation('store', $store);
            $transaction->setRelation('requestedByUser', $user);
            $transaction->setRelation('paymentMethod', $paymentMethod);

            $companyToken = config('app.dpo_company_token');
            $dpoPaymentLinkPayload = $this->prepareDpoPaymentLinkPayload($user, $transaction, $domain);
            $metadata = DirectPayOnlineService::createPaymentLink($companyToken, $dpoPaymentLinkPayload);

            $transaction->update(['metadata' => $metadata]);
            return (new TransactionService())->showResource($transaction);
        } catch (Exception $e) {
            Log::error('Failed to purchase domain: ' . $e->getMessage());
            return [
                'successful' => false,
                'message' => $e->getMessage()
            ];
        }
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

        if ($domain instanceof Domain) {
            if ($connected) {
                $domain->update([
                    'status' => DomainStatus::CONNECTED->value,
                    'verified_at' => now()
                ]);
            } else {
                $domain->update(['status' => DomainStatus::PENDING->value]);
            }

            return $domain;
        } else {
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

    /**
     * Prepare transaction payload.
     *
     * @param $user
     * @param Store $store
     * @param Domain $domain
     * @param float $price
     * @param PaymentMethod $paymentMethod
     * @return array
     */
    private function prepareTransactionPayload($user, $store, $domain, $price, $paymentMethod): array
    {
        return [
            'amount' => $price,
            'percentage' => 100,
            'currency' => 'USD',
            'store_id' => $store->id,
            'owner_type' => 'domain',
            'owner_id' => $domain->id,
            'requested_by_user_id' => $user->id,
            'created_using_auto_billing' => false,
            'payment_method_id' => $paymentMethod->id,
            'description' => "Purchase of domain: {$domain->name}",
            'payment_status' => TransactionPaymentStatus::PENDING_PAYMENT->value,
            'verification_type' => TransactionVerificationType::AUTOMATIC->value
        ];
    }

    /**
     * Prepare DPO payment link payload.
     *
     * @param $user
     * @param Transaction $transaction
     * @param Domain $domain
     * @return array
     */
    private function prepareDpoPaymentLinkPayload($user, $transaction, $domain): array
    {
        $customerPhone = $customerCountry = $customerDialCode = null;
        $redirectUrl = config('app.url') . '/dashboard/domains/verify-payment?transaction_id=' . $transaction->id . '&store_id=' . $transaction->store_id;

        if ($user->mobile_number) {
            $customerCountry = $customerDialCode = $user->mobile_number->getCountry();
            $customerPhone = PhoneNumberService::getNationalPhoneNumberWithoutSpaces($user->mobile_number);
        }

        return [
            'ptl' => 24,
            'ptlType' => 'hours',
            'companyRefUnique' => 1,
            'emailTransaction' => true,
            'paymentCurrency' => 'USD',
            'redirectURL' => $redirectUrl,
            'backURL' => url()->previous(),
            'customerEmail' => $user->email,
            'companyRef' => $transaction->id,
            'customerPhone' => $customerPhone,
            'companyAccRef' => 'Domain Purchase',
            'customerCountry' => $customerCountry,
            'customerLastName' => $user->last_name,
            'customerDialCode' => $customerDialCode,
            'paymentAmount' => $transaction->amount,
            'customerFirstName' => $user->first_name,
            'emailTransaction' => !empty($user->email),
            'metadata' => ['Transaction ID' => $transaction->id, 'Domain Name' => $domain->name],
            'services' => [
                [
                    'serviceDescription' => "Purchase of domain: {$domain->name}",
                    'serviceDate' => now()->format('Y/m/d H:i')
                ]
            ]
        ];
    }
}
