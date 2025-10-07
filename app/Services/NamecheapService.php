<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

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

            $errorNumber = $result['Errors']['Error']['Number'] ?? null;
            $errorMessage = $result['Errors']['Error'] ?? 'Unknown error';

            if($errorNumber) {
                throw new Exception("Namecheap: {$errorNumber} - {$errorMessage}");
            }else{
                throw new Exception("Namecheap: {$errorMessage}");
            }
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
            'discount_percentage' => 0,
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
                                    $result['discount_percentage'] = $result['price'] && $result['regular_price']
                                        ? round((1 - $result['price'] / $result['regular_price']) * 100)
                                        : 0;
                                } elseif ($action === 'RENEW') {
                                    $result['renewal_price'] = (float) ($price['@attributes']['Price'] ?? 0);
                                }
                            }
                        }
                    }
                }
            } else {

                $errorNumber = $result['Errors']['Error']['Number'] ?? null;
                $errorMessage = $result['Errors']['Error'] ?? 'Unknown error';

                if($errorNumber) {
                    throw new Exception("Namecheap: {$errorNumber} - {$errorMessage}");
                }else{
                    throw new Exception("Namecheap: {$errorMessage}");
                }

            }
        }

        return $result;
    }

    /**
     * Purchase a domain.
     *
     * @param string $domainName
     * @param array $userProfile
     * @return array
     * @throws Exception
     */
    public static function purchaseDomain(string $domainName, array $userProfile): array
    {
        $apiKey = config('services.namecheap.api_key');
        $apiUrl = config('services.namecheap.api_url');
        $username = config('services.namecheap.username');
        $clientIp = request()->ip() === '127.0.0.1' ? config('services.namecheap.client_ip', '127.0.0.1') : request()->ip();

        // Format phone number to match +NNN.NNNNNNNNNN
        $phone = preg_replace('/^(\+\d{1,3})(\d{7,})$/', '$1.$2', $userProfile['phone']);

        $params = [
            'ApiKey' => $apiKey,
            'ApiUser' => $username,
            'UserName' => $username,
            'ClientIp' => $clientIp,
            'Command' => 'namecheap.domains.create',

            'Years' => 1,
            'DomainName' => $domainName,

            'RegistrantPhone' => $phone,
            'RegistrantCity' => $userProfile['city'],
            'RegistrantCountry' => $userProfile['country'],
            'RegistrantAddress1' => $userProfile['address1'],
            'RegistrantEmailAddress' => $userProfile['email'],
            'RegistrantLastName' => $userProfile['last_name'],
            'RegistrantStateProvince' => $userProfile['state'],
            'RegistrantFirstName' => $userProfile['first_name'],
            'RegistrantPostalCode' => $userProfile['postal_code'],

            'TechPhone' => $phone,
            'TechCity' => $userProfile['city'],
            'TechCountry' => $userProfile['country'],
            'TechAddress1' => $userProfile['address1'],
            'TechLastName' => $userProfile['last_name'],
            'TechEmailAddress' => $userProfile['email'],
            'TechStateProvince' => $userProfile['state'],
            'TechFirstName' => $userProfile['first_name'],
            'TechPostalCode' => $userProfile['postal_code'],

            'AdminPhone' => $phone,
            'AdminCity' => $userProfile['city'],
            'AdminCountry' => $userProfile['country'],
            'AdminAddress1' => $userProfile['address1'],
            'AdminLastName' => $userProfile['last_name'],
            'AdminEmailAddress' => $userProfile['email'],
            'AdminFirstName' => $userProfile['first_name'],
            'AdminStateProvince' => $userProfile['state'],
            'AdminPostalCode' => $userProfile['postal_code'],

            'AuxBillingPhone' => $phone,
            'AuxBillingCity' => $userProfile['city'],
            'AuxBillingCountry' => $userProfile['country'],
            'AuxBillingAddress1' => $userProfile['address1'],
            'AuxBillingEmailAddress' => $userProfile['email'],
            'AuxBillingLastName' => $userProfile['last_name'],
            'AuxBillingStateProvince' => $userProfile['state'],
            'AuxBillingFirstName' => $userProfile['first_name'],
            'AuxBillingPostalCode' => $userProfile['postal_code'],
        ];

        // Build query string with proper URL encoding
        $queryString = http_build_query($params);
        $fullUrl = "{$apiUrl}?{$queryString}";

        // Send POST request with query parameters
        $response = Http::post($fullUrl);

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

            $errorNumber = $result['Errors']['Error']['Number'] ?? null;
            $errorMessage = $result['Errors']['Error'] ?? 'Unknown error';

            if($errorNumber) {
                throw new Exception("Namecheap: {$errorNumber} - {$errorMessage}");
            }else{
                throw new Exception("Namecheap: {$errorMessage}");
            }
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
        $clientIp = request()->ip() === '127.0.0.1' ? config('services.namecheap.client_ip', '127.0.0.1') : request()->ip();

        $sld = explode('.', $domainName)[0];
        $tld = explode('.', $domainName)[1];

        $params = [
            'Command' => 'namecheap.domains.dns.setHosts',
            'ApiUser' => $username,
            'ApiKey' => $apiKey,
            'UserName' => $username,
            'ClientIp' => $clientIp,
            'SLD' => $sld,
            'TLD' => $tld,
            'HostName1' => '@',
            'RecordType1' => 'A',
            'Address1' => $serverIp,
            'TTL1' => '1800',
        ];

        // Build query string with proper URL encoding
        $queryString = http_build_query($params);
        $fullUrl = "{$apiUrl}?{$queryString}";

        // Send POST request with query parameters
        $response = Http::post($fullUrl);

        if ($response->successful()) {

            $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);

            if ($xml === false) {
                throw new Exception('Failed to parse Namecheap API XML response');
            }

            $json = json_encode($xml);
            $result = json_decode($json, true);

            if ($result['@attributes']['Status'] === 'OK') {
                if ($result['CommandResponse']['DomainDNSSetHostsResult']['@attributes']['IsSuccess'] === 'true') {
                    return;
                }
                throw new Exception('Namecheap: Failed to set DNS host records');
            }

            $errorNumber = $result['Errors']['Error']['Number'] ?? null;
            $errorMessage = $result['Errors']['Error'] ?? 'Unknown error';

            if ($errorNumber) {
                throw new Exception("Namecheap: {$errorNumber} - {$errorMessage}");
            } else {
                throw new Exception("Namecheap: {$errorMessage}");
            }
        }

        throw new Exception('Failed to connect to Namecheap API: HTTP ' . $response->status());
    }

    /**
     * Check DNS records for a domain.
     *
     * @param string $domainName
     * @return array
     * @throws Exception
     */
    public static function checkDnsRecords(string $domainName): array
    {
        $apiKey = config('services.namecheap.api_key');
        $apiUrl = config('services.namecheap.api_url');
        $username = config('services.namecheap.username');

        $response = Http::get("{$apiUrl}", [
            'ApiKey' => $apiKey,
            'ApiUser' => $username,
            'UserName' => $username,
            'Command' => 'namecheap.domains.dns.getHosts',
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

                $hosts = $result['CommandResponse']['DomainDNSGetHostsResult']['host'] ?? [];

                $aRecord = null;

                // Normalize hosts to always be an array of host records
                $hostRecords = [];
                if (!empty($hosts)) {
                    if (isset($hosts['@attributes'])) {
                        // Single host record
                        $hostRecords = [$hosts];
                    } else {
                        // Multiple host records
                        $hostRecords = $hosts;
                    }
                }

                foreach ($hostRecords as $index => $host) {

                    if (!isset($host['@attributes'])) {
                        continue;
                    }

                    $type = $host['@attributes']['Type'] ?? '';
                    $name = $host['@attributes']['Name'] ?? '';

                    if ($type === 'A' && $name === '@') {
                        $aRecord = $host['@attributes']['Address'] ?? null;
                        break;
                    }
                }

                return ['A' => $aRecord];
            }

            $errorNumber = $result['Errors']['Error']['Number'] ?? null;
            $errorMessage = $result['Errors']['Error'] ?? 'Unknown error';

            if ($errorNumber) {
                throw new Exception("Namecheap: {$errorNumber} - {$errorMessage}");
            } else {
                throw new Exception("Namecheap: {$errorMessage}");
            }
        }

        throw new Exception('Failed to connect to Namecheap API: HTTP ' . $response->status());
    }

    /**
     * Retrieve contact information for a domain.
     *
     * @param string $domainName
     * @return array
     * @throws Exception
     */
    public static function getDomainContacts(string $domainName): array
    {
        $apiKey = config('services.namecheap.api_key');
        $apiUrl = config('services.namecheap.api_url');
        $username = config('services.namecheap.username');
        $clientIp = request()->ip() === '127.0.0.1' ? config('services.namecheap.client_ip', '127.0.0.1') : request()->ip();

        // Validate domain format
        if (!preg_match('/^([a-zA-Z0-9.-]+)\.[a-zA-Z]{2,}$/', $domainName)) {
            throw new Exception('Invalid domain format: Must be a valid domain name (e.g., example.com)');
        }

        // Split domain into SLD and TLD
        $sld = explode('.', $domainName)[0];
        $tld = explode('.', $domainName)[1];

        // Build query parameters
        $params = [
            'SLD' => $sld,
            'TLD' => $tld,
            'ApiKey' => $apiKey,
            'ApiUser' => $username,
            'UserName' => $username,
            'ClientIp' => $clientIp,
            'DomainName' => $domainName,
            'Command' => 'namecheap.domains.getContacts',
        ];

        // Send GET request
        $response = Http::timeout(60)->retry(3, 1000)->get($apiUrl, $params);

        if ($response->successful()) {

            $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);

            if ($xml === false) {
                throw new Exception('Failed to parse Namecheap API XML response');
            }

            $json = json_encode($xml);
            $result = json_decode($json, true);

            if (isset($result['@attributes']['Status']) && $result['@attributes']['Status'] === 'OK') {
                $contactInfo = $result['CommandResponse']['DomainContactsResult'] ?? [];

                // Extract all contact types
                $registrant = $contactInfo['Registrant'] ?? [];
                $tech = $contactInfo['Tech'] ?? [];
                $admin = $contactInfo['Admin'] ?? [];
                $auxBilling = $contactInfo['AuxBilling'] ?? [];

                // Format the response to include all contact fields
                return [
                    'registrant' => [
                        'first_name' => $registrant['FirstName'] ?? null,
                        'last_name' => $registrant['LastName'] ?? null,
                        'email' => $registrant['EmailAddress'] ?? null,
                        'phone' => $registrant['Phone'] ?? null,
                        'address1' => $registrant['Address1'] ?? null,
                        'city' => $registrant['City'] ?? null,
                        'state' => $registrant['StateProvince'] ?? null,
                        'country' => $registrant['Country'] ?? null,
                        'postal_code' => $registrant['PostalCode'] ?? null,
                    ],
                    'tech' => [
                        'first_name' => $tech['FirstName'] ?? null,
                        'last_name' => $tech['LastName'] ?? null,
                        'email' => $tech['EmailAddress'] ?? null,
                        'phone' => $tech['Phone'] ?? null,
                        'address1' => $tech['Address1'] ?? null,
                        'city' => $tech['City'] ?? null,
                        'state' => $tech['StateProvince'] ?? null,
                        'country' => $tech['Country'] ?? null,
                        'postal_code' => $tech['PostalCode'] ?? null,
                    ],
                    'admin' => [
                        'first_name' => $admin['FirstName'] ?? null,
                        'last_name' => $admin['LastName'] ?? null,
                        'email' => $admin['EmailAddress'] ?? null,
                        'phone' => $admin['Phone'] ?? null,
                        'address1' => $admin['Address1'] ?? null,
                        'city' => $admin['City'] ?? null,
                        'state' => $admin['StateProvince'] ?? null,
                        'country' => $admin['Country'] ?? null,
                        'postal_code' => $admin['PostalCode'] ?? null,
                    ],
                    'aux_billing' => [
                        'first_name' => $auxBilling['FirstName'] ?? null,
                        'last_name' => $auxBilling['LastName'] ?? null,
                        'email' => $auxBilling['EmailAddress'] ?? null,
                        'phone' => $auxBilling['Phone'] ?? null,
                        'address1' => $auxBilling['Address1'] ?? null,
                        'city' => $auxBilling['City'] ?? null,
                        'state' => $auxBilling['StateProvince'] ?? null,
                        'country' => $auxBilling['Country'] ?? null,
                        'postal_code' => $auxBilling['PostalCode'] ?? null,
                    ],
                ];
            }

            $errorNumber = $result['Errors']['Error']['Number'] ?? null;
            $errorMessage = $result['Errors']['Error'] ?? 'Unknown error';

            if ($errorNumber) {
                throw new Exception("Namecheap: {$errorNumber} - {$errorMessage}");
            } else {
                throw new Exception("Namecheap: {$errorMessage}");
            }
        }

        throw new Exception('Failed to connect to Namecheap API: HTTP ' . $response->status());
    }
}
