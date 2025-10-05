<?php

namespace App\Services;

use App\Enums\CacheName;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NamecheapService
{
    /**
     * Search for the availability of specific domains.
     *
     * @param string $searchTerm
     * @return array
     * @throws Exception
     */
    public static function searchDomains(string $searchTerm): array
    {
        $apiKey = config('services.namecheap.api_key');
        $username = config('services.namecheap.username');
        $apiUrl = config('services.namecheap.api_url');

        // Validate search term
        if (empty(trim($searchTerm)) || strlen($searchTerm) < 3) {
            throw new Exception('Invalid search term: Must be at least 3 characters');
        }

        // Validate domain format
        $domainsToCheck = explode(',', $searchTerm);
        foreach ($domainsToCheck as $domain) {
            if (!preg_match('/^([a-zA-Z0-9.-]+)\.[a-zA-Z]{2,}$/', trim($domain))) {
                throw new Exception('Invalid domain format: Must be valid domain names (e.g., example.com)');
            }
        }
        $domainList = implode(',', array_map('trim', $domainsToCheck));

        // Get valid client IP
        $clientIp = request()->ip() === '127.0.0.1' ? config('services.namecheap.client_ip', '127.0.0.1') : request()->ip();

        // Make availability request
        $response = Http::timeout(60)->retry(3, 1000)->get("{$apiUrl}?ApiUser={$username}&ApiKey={$apiKey}&UserName={$username}&Command=namecheap.domains.check&ClientIp={$clientIp}&DomainList={$domainList}");

        if ($response->successful()) {
            $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($xml === false) {
                throw new Exception('Failed to parse Namecheap API XML response');
            }

            $json = json_encode($xml);
            $result = json_decode($json, true);

            if (isset($result['@attributes']['Status']) && $result['@attributes']['Status'] === 'OK') {
                $domains = $result['CommandResponse']['DomainCheckResult'] ?? [];
                if (isset($domains['@attributes'])) {
                    $domains = [$domains];
                }

                $formattedDomains = array_map(function ($domainData) {
                    $domain = $domainData['@attributes'];
                    $isPremium = isset($domain['IsPremiumName']) && $domain['IsPremiumName'] === 'true';
                    return [
                        'name' => $domain['Domain'],
                        'available' => $domain['Available'] === 'true',
                        'is_premium' => $isPremium,
                        'premium_price' => $isPremium ? (float) ($domain['PremiumRegistrationPrice'] ?? 0) : null
                    ];
                }, $domains);

                return array_values(array_unique($formattedDomains, SORT_REGULAR));
            }

            $errorNumber = $result['Errors']['Error']['Number'] ?? 'Unknown error';
            $errorMessage = $result['Errors']['Error'] ?? 'Unknown error';
            throw new Exception("Namecheap API error: {$errorNumber} - {$errorMessage}");
        }

        throw new Exception('Failed to connect to Namecheap API: HTTP ' . $response->status());
    }


    /**
     * Get pricing for a specific TLD (cached for 1 hour).
     *
     * @param string $tld
     * @return array
     * @throws Exception
     */
    public static function getDomainPricing(string $tld): array
    {
        $apiKey = config('services.namecheap.api_key');
        $apiUrl = config('services.namecheap.api_url');
        $username = config('services.namecheap.username');
        $clientIp = request()->ip() === '127.0.0.1' ? config('services.namecheap.client_ip', '127.0.0.1') : request()->ip();

        // Validate TLD
        if ($tld && !preg_match('/^[a-zA-Z]{2,}$/', $tld)) {
            throw new Exception('Invalid TLD format: ' . $tld);
        }

        // Initialize result
        $result = [
            'tld' => $tld,
            'price' => 0.00,
            'min_duration' => 1,
            'renewal_price' => 0.00,
            'regular_price' => 0.00,
        ];

        // Fetch prices for both REGISTER and RENEW actions
        $actions = ['REGISTER', 'RENEW'];
        foreach ($actions as $action) {
            $query = [
                'ApiKey' => $apiKey,
                'ProductName' => $tld,
                'ApiUser' => $username,
                'UserName' => $username,
                'ClientIp' => $clientIp,
                'ActionName' => $action,
                'ProductType' => 'DOMAIN',
                'ProductCategory' => 'DOMAINS',
                'Command' => 'namecheap.users.getPricing'
            ];

            $response = Http::timeout(60)->retry(3, 1000)->get($apiUrl, $query);

            if (!$response->successful()) {
                throw new Exception('Failed to fetch Namecheap pricing for ' . $action . ': HTTP ' . $response->status());
            }

            $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);

            if ($xml === false) {
                throw new Exception('Failed to parse Namecheap Pricing XML response for ' . $action);
            }

            $json = json_encode($xml);
            $data = json_decode($json, true);

            if (isset($data['@attributes']['Status']) && $data['@attributes']['Status'] === 'OK') {
                $products = $data['CommandResponse']['UserGetPricingResult']['ProductType']['ProductCategory'] ?? [];

                if (isset($products['@attributes'])) {
                    $products = [$products];
                }

                foreach ($products as $product) {
                    if (isset($product['@attributes']['Name']) && strtoupper($product['@attributes']['Name']) === $action) {
                        $prices = $product['Product']['Price'] ?? [];

                        if (isset($prices['@attributes'])) {
                            $prices = [$prices];
                        }

                        // Find the minimum duration (e.g., 2 years for .ai)
                        $minDuration = min(array_map(function ($price) {
                            return (int) $price['@attributes']['Duration'];
                        }, $prices));

                        foreach ($prices as $price) {
                            $duration = (int) $price['@attributes']['Duration'];
                            $durationType = $price['@attributes']['DurationType'];

                            if ($durationType === 'YEAR' && $duration === $minDuration) {
                                if ($action === 'REGISTER') {
                                    $result['min_duration'] = $minDuration;
                                    $result['price'] = (float) ($price['@attributes']['Price'] ?? 0);
                                    $result['regular_price'] = (float) ($price['@attributes']['RegularPrice'] ?? 0);
                                } elseif ($action === 'RENEW') {
                                    $result['renewal_price'] = (float) ($price['@attributes']['Price'] ?? 0);
                                }
                            }
                        }
                    }
                }
            } else {
                $errorNumber = $data['Errors']['Error']['Number'] ?? 'Unknown error';
                $errorMessage = $data['Errors']['Error'] ?? 'Unknown error';
                throw new Exception("Namecheap getPricing API error for $action: {$errorNumber} - {$errorMessage}");
            }
        }

        return $result;
    }

    /**
     * Purchase a domain.
     *
     * @param string $domainName
     * @param array $userData
     * @return array
     * @throws Exception
     */
    public static function purchaseDomain(string $domainName, array $userData): array
    {
        $apiKey = config('services.namecheap.api_key');
        $apiUrl = config('services.namecheap.api_url');
        $username = config('services.namecheap.username');

        $response = Http::post("{$apiUrl}?Command=namecheap.domains.create", [
            'ApiUser' => $username,
            'ApiKey' => $apiKey,
            'UserName' => $username,
            'ClientIp' => request()->ip() === '127.0.0.1' ? config('services.namecheap.client_ip', '127.0.0.1') : request()->ip(),
            'DomainName' => $domainName,
            'Years' => 1,
            'RegistrantFirstName' => $userData['first_name'],
            'RegistrantLastName' => $userData['last_name'],
            'RegistrantAddress1' => $userData['address1'] ?? 'Unknown',
            'RegistrantCity' => $userData['city'] ?? 'Unknown',
            'RegistrantStateProvince' => $userData['state'] ?? 'Unknown',
            'RegistrantPostalCode' => $userData['postal_code'] ?? '00000',
            'RegistrantCountry' => $userData['country'] ?? 'US',
            'RegistrantPhone' => $userData['phone'] ?? '',
            'RegistrantEmailAddress' => $userData['email'],
            'TechFirstName' => $userData['first_name'],
            'TechLastName' => $userData['last_name'],
            'TechAddress1' => $userData['address1'] ?? 'Unknown',
            'TechCity' => $userData['city'] ?? 'Unknown',
            'TechStateProvince' => $userData['state'] ?? 'Unknown',
            'TechPostalCode' => $userData['postal_code'] ?? '00000',
            'TechCountry' => $userData['country'] ?? 'US',
            'TechPhone' => $userData['phone'] ?? '',
            'TechEmailAddress' => $userData['email'],
            'AdminFirstName' => $userData['first_name'],
            'AdminLastName' => $userData['last_name'],
            'AdminAddress1' => $userData['address1'] ?? 'Unknown',
            'AdminCity' => $userData['city'] ?? 'Unknown',
            'AdminStateProvince' => $userData['state'] ?? 'Unknown',
            'AdminPostalCode' => $userData['postal_code'] ?? '00000',
            'AdminCountry' => $userData['country'] ?? 'US',
            'AdminPhone' => $userData['phone'] ?? '',
            'AdminEmailAddress' => $userData['email'],
            'AuxBillingFirstName' => $userData['first_name'],
            'AuxBillingLastName' => $userData['last_name'],
            'AuxBillingAddress1' => $userData['address1'] ?? 'Unknown',
            'AuxBillingCity' => $userData['city'] ?? 'Unknown',
            'AuxBillingStateProvince' => $userData['state'] ?? 'Unknown',
            'AuxBillingPostalCode' => $userData['postal_code'] ?? '00000',
            'AuxBillingCountry' => $userData['country'] ?? 'US',
            'AuxBillingPhone' => $userData['phone'] ?? '',
            'AuxBillingEmailAddress' => $userData['email']
        ]);

        if ($response->successful()) {
            $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($xml === false) {
                throw new Exception('Failed to parse Namecheap API XML response');
            }

            $json = json_encode($xml);
            $result = json_decode($json, true);

            if ($result['@attributes']['Status'] === 'OK') {
                return $result['CommandResponse'];
            }

            $errorNumber = $result['Errors']['Error']['Number'] ?? 'Unknown error';
            $errorMessage = $result['Errors']['Error'] ?? 'Unknown error';
            throw new Exception("Namecheap API error: {$errorNumber} - {$errorMessage}");
        }

        throw new Exception('Failed to connect to Namecheap API: HTTP ' . $response->status());
    }

    /**
     * Configure DNS settings for a domain.
     *
     * @param string $domainName
     * @param string $serverIp
     * @return void
     * @throws Exception
     */
    public static function configureDnsSettings(string $domainName, string $serverIp): void
    {
        $apiKey = config('services.namecheap.api_key');
        $apiUrl = config('services.namecheap.api_url');
        $username = config('services.namecheap.username');

        $sld = explode('.', $domainName)[0];
        $tld = explode('.', $domainName)[1];

        $response = Http::post("{$apiUrl}?Command=namecheap.domains.dns.setHosts", [
            'ApiUser' => $username,
            'ApiKey' => $apiKey,
            'UserName' => $username,
            'ClientIp' => request()->ip() === '127.0.0.1' ? config('services.namecheap.client_ip', '127.0.0.1') : request()->ip(),
            'SLD' => $sld,
            'TLD' => $tld,
            'HostName1' => '@',
            'RecordType1' => 'A',
            'Address1' => $serverIp,
            'TTL1' => 1800
        ]);

        if ($response->successful()) {
            $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($xml === false) {
                throw new Exception('Failed to parse Namecheap API XML response');
            }

            $json = json_encode($xml);
            $result = json_decode($json, true);

            if ($result['@attributes']['Status'] !== 'OK') {
                $errorNumber = $result['Errors']['Error']['Number'] ?? 'Unknown error';
                $errorMessage = $result['Errors']['Error'] ?? 'Unknown error';
                throw new Exception("Namecheap API error: {$errorNumber} - {$errorMessage}");
            }
        } else {
            throw new Exception('Failed to connect to Namecheap API: HTTP ' . $response->status());
        }
    }

    /**
     * Check DNS records for a domain.
     *
     * @param string $domainName
     * @param string $serverIp
     * @return array
     * @throws Exception
     */
    public static function checkDnsRecords(string $domainName, string $serverIp): array
    {
        $apiKey = config('services.namecheap.api_key');
        $username = config('services.namecheap.username');
        $apiUrl = config('services.namecheap.api_url');

        $response = Http::get("{$apiUrl}?Command=namecheap.domains.dns.getHosts", [
            'ApiUser' => $username,
            'ApiKey' => $apiKey,
            'UserName' => $username,
            'ClientIp' => request()->ip() === '127.0.0.1' ? config('services.namecheap.client_ip', '127.0.0.1') : request()->ip(),
            'SLD' => explode('.', $domainName)[0],
            'TLD' => explode('.', $domainName)[1]
        ]);

        if ($response->successful()) {
            $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($xml === false) {
                throw new Exception('Failed to parse Namecheap API XML response');
            }

            $json = json_encode($xml);
            $result = json_decode($json, true);

            if ($result['@attributes']['Status'] === 'OK') {
                $hosts = $result['CommandResponse']['DomainDNSGetHostsResult']['Host'] ?? [];
                if (isset($hosts['@attributes'])) {
                    $hosts = [$hosts];
                }
                $aRecord = null;
                foreach ($hosts as $host) {
                    if ($host['@attributes']['Type'] === 'A' && $host['@attributes']['Name'] === '@') {
                        $aRecord = $host['@attributes']['Address'];
                        break;
                    }
                }
                return ['A' => $aRecord];
            }

            $errorNumber = $result['Errors']['Error']['Number'] ?? 'Unknown error';
            $errorMessage = $result['Errors']['Error'] ?? 'Unknown error';
            throw new Exception("Namecheap API error: {$errorNumber} - {$errorMessage}");
        }

        throw new Exception('Failed to connect to Namecheap API: HTTP ' . $response->status());
    }
}
