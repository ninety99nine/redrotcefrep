<?php

namespace App\Services;

use Exception;
use App\Models\Store;
use App\Models\Domain;
use App\Enums\DomainType;
use App\Enums\DomainStatus;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Enums\PaymentMethodType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Enums\TransactionFailureType;
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

            $existingDomain = $store->domains()->where('name', $name)->first();

            if ($existingDomain && $existingDomain->status === DomainStatus::CONNECTED->value) {
                throw new Exception('This domain already exists');
            } else if ($existingDomain && $existingDomain->status === DomainStatus::PENDING->value) {
                $domain = $existingDomain;
            } else {
                $domain = $store->domains()->create(['name' => $name]);
            }

            $transactionPayload = $this->prepareTransactionPayload($user, $store, $domain, $price, $paymentMethod);
            $transaction = Transaction::create($transactionPayload);

            $transaction->setRelation('store', $store);
            $transaction->setRelation('owner', $domain);
            $transaction->setRelation('requestedByUser', $user);
            $transaction->setRelation('paymentMethod', $paymentMethod);

            $companyToken = config('app.dpo_company_token');
            $dpoPaymentLinkPayload = $this->prepareDpoPaymentLinkPayload($user, $store, $transaction, $domain);
            $metadata = DirectPayOnlineService::createPaymentLink($companyToken, $dpoPaymentLinkPayload);

            $transaction->update([
                'metadata' => array_merge(
                    $metadata,
                    [
                        'user_profile' => [
                            'city' => $data['city'],
                            'state' => $data['state'],
                            'email' => $data['email'],
                            'phone' => $data['phone'],
                            'country' => $data['country'],
                            'address1' => $data['address1'],
                            'last_name' => $data['last_name'],
                            'first_name' => $data['first_name'],
                            'postal_code' => $data['postal_code']
                        ]
                    ]
                )
            ]);

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
     * Verify domain payment.
     *
     * @param Transaction $transaction
     * @return TransactionResource
     * @throws Exception
     */
    public function verifyDomainPayment(Transaction $transaction): TransactionResource
    {
        try {

            $transaction = $transaction->load(['owner', 'paymentMethod']);

            if (!$transaction->isPaid()) {

                $domain = $transaction->owner;
                $paymentMethod = $transaction->paymentMethod;

                if (!$domain || $transaction->owner_type !== 'domain') {
                    throw new Exception('The transaction is not associated with a domain');
                }

                if (!$paymentMethod) {
                    throw new Exception('The transaction payment method does not exist');
                }

                if ($paymentMethod->type == PaymentMethodType::DPO->value) {

                    $companyToken = config('app.dpo_company_token');
                    $transactionToken = $transaction->metadata['dpo_transaction_token'];
                    $metadata = DirectPayOnlineService::verifyPayment($companyToken, $transactionToken);

                    $userProfile = $transaction->metadata['user_profile'] ?? [];
                    $namecheapResponse = NamecheapService::purchaseDomain($domain->name, $userProfile);

                    $serverIp = $this->resolveServerIp();
                    NamecheapService::configureDnsSettings($domain->name, $serverIp);

                    $domain->update([
                        'status' => DomainStatus::PROCESSING->value,
                        'type' => DomainType::PURCHASED->value
                    ]);

                    $this->verifyConnection($domain);

                    $transaction->update([
                        'failure_type' => null,
                        'failure_reason' => null,
                        'payment_status' => TransactionPaymentStatus::PAID->value,
                        'metadata' => array_merge($transaction->metadata, $metadata, [
                            'namecheap_response' => $namecheapResponse
                        ])
                    ]);

                } else {
                    throw new Exception('The ' . $paymentMethod->name . ' payment method cannot be used to verify transaction payment');
                }
            }

            return (new TransactionService)->showResource($transaction);

        } catch (Exception $e) {

            $transaction->update([
                'failure_reason' => $e->getMessage(),
                'payment_status' => TransactionPaymentStatus::FAILED_PAYMENT->value,
                'failure_type' => TransactionFailureType::PAYMENT_VERIFICATION_FAILED->value
            ]);

            throw $e;

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
        if($domain->type === DomainType::PURCHASED->value) {
            throw new Exception('Purchased domains cannot be modified');
        }

        $domain->update($data);
        $verifiedDomain = $this->verifyConnection($domain);
        return $this->showUpdatedResource($verifiedDomain);
    }

    /**
     * Show domain contacts.
     *
     * @param Domain $domain
     * @return array
     */
    public function showDomainContacts(Domain $domain): array
    {
        return NamecheapService::getDomainContacts($domain->name);
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

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Domain deleted' : 'Domain delete unsuccessful'
        ];
    }

    /**
     * Verify domain connection.
     *
     * @param string|Domain $name
     * @return bool|Domain
     */
    public function verifyConnection(string|Domain $domain): bool|Domain
    {
        try {

            $serverIp = $this->resolveServerIp();
            $domainName = $domain instanceof Domain ? $domain->name : $domain;

            // Attempt to resolve A record using dns_get_record
            $resolvedIp = null;
            $records = dns_get_record($domainName, DNS_A);

            if (!empty($records) && isset($records[0]['ip']) && filter_var($records[0]['ip'], FILTER_VALIDATE_IP)) {
                $resolvedIp = $records[0]['ip'];
            }

            // Validate resolved IP
            $isValidIp = filter_var($resolvedIp, FILTER_VALIDATE_IP);
            $connected = $isValidIp && $resolvedIp === $serverIp;

            // Validate resolved IP
            $connected = $resolvedIp === $serverIp;

            if(!$connected) {

                // Attempt to resolve A record using NamecheapService (Assuming the domain was purchased via NamecheapService)
                $dnsRecords = NamecheapService::checkDnsRecords($domainName);
                $connected = $dnsRecords['A'] === $serverIp;

            }

            if ($domain instanceof Domain) {
                if ($connected) {
                    $domain->update([
                        'verified_at' => now(),
                        'last_verification_attempt_at' => now(),
                        'status' => DomainStatus::CONNECTED->value,
                    ]);
                } else {
                    $domain->update([
                        'last_verification_attempt_at' => now(),
                        'status' => $domain->status === DomainStatus::PROCESSING->value ? DomainStatus::PROCESSING->value : DomainStatus::PENDING->value
                    ]);
                }

                return $domain;
            } else {
                return $connected;
            }

        } catch (Exception $e) {

            if ($domain instanceof Domain) {

                $domain->update([
                    'last_verification_attempt_at' => now(),
                    'status' => $domain->status === DomainStatus::PROCESSING->value ? DomainStatus::PROCESSING->value : DomainStatus::PENDING->value
                ]);

                return $domain;

            }

            return false;

        }
    }

    /**
     * Resolve server IP from APP_URL.
     *
     * @return string
     * @throws Exception
     */
    public function resolveServerIp(): string
    {
        $appUrl = config('app.url');
        $hostname = parse_url($appUrl, PHP_URL_HOST);

        if (!$hostname) {
            throw new Exception('Unable to parse hostname from APP_URL: ' . $appUrl);
        }

        return Cache::remember('app_server_ip', now()->addHour(), function () use ($hostname) {
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
            'description' => "Purchasing domain {$domain->name}",
            'payment_status' => TransactionPaymentStatus::PENDING_PAYMENT->value,
            'verification_type' => TransactionVerificationType::AUTOMATIC->value
        ];
    }

    /**
     * Prepare DPO payment link payload.
     *
     * @param $user
     * @param Store $store
     * @param Transaction $transaction
     * @param Domain $domain
     * @return array
     */
    private function prepareDpoPaymentLinkPayload($user, $store, $transaction, $domain): array
    {
        $customerPhone = $customerCountry = $customerDialCode = null;
        $redirectUrl = config('app.url') . '/dashboard/settings/domains/verify-payment?transaction_id=' . $transaction->id . '&store_id=' . $transaction->store_id;

        if ($user->mobile_number) {
            $customerCountry = $customerDialCode = $user->mobile_number->getCountry();
            $customerPhone = PhoneNumberService::getNationalPhoneNumberWithoutSpaces($user->mobile_number);
        }

        return [
            'ptl' => 24,
            'ptlType' => 'hours',
            'companyRefUnique' => 1,
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
            'customerFirstName' => $user->first_name,
            'emailTransaction' => !empty($user->email),
            'paymentAmount' => $transaction->amount->amount_without_currency,
            'metadata' => [
                'Transaction ID' => $transaction->id,
                'Domain Name' => $domain->name,
                'Store Name' => $store->name,
                'Store ID' => $store->id,
            ],
            'services' => [
                [
                    'serviceDescription' => "Buying domain {$domain->name}",
                    'serviceDate' => now()->format('Y/m/d H:i')
                ]
            ]
        ];
    }
}
