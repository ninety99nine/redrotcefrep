<?php

namespace App\Services;

use stdClass;
use Exception;
use App\Enums\CacheName;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MoneyService
{
    /**
     * Get currencies.
     *
     * @return array
     */
    public static function getCurrencies(): array
    {
        return (new CacheService(CacheName::CURRENCIES))->rememberForever(function () {
            return [
                'USD' => [
                    'symbol' => '$',
                    'name' => 'US Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'code' => 'USD',
                ],
                'CAD' => [
                    'symbol' => 'CA$',
                    'name' => 'Canadian Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'code' => 'CAD',
                ],
                'EUR' => [
                    'symbol' => '€',
                    'name' => 'Euro',
                    'symbol_native' => '€',
                    'decimal_digits' => 2,
                    'code' => 'EUR',
                ],
                'AED' => [
                    'symbol' => 'AED',
                    'name' => 'United Arab Emirates Dirham',
                    'symbol_native' => 'د.إ.‏',
                    'decimal_digits' => 2,
                    'code' => 'AED',
                ],
                'AFN' => [
                    'symbol' => 'Af',
                    'name' => 'Afghan Afghani',
                    'symbol_native' => '؋',
                    'decimal_digits' => 0,
                    'code' => 'AFN',
                ],
                'ALL' => [
                    'symbol' => 'ALL',
                    'name' => 'Albanian Lek',
                    'symbol_native' => 'Lek',
                    'decimal_digits' => 0,
                    'code' => 'ALL',
                ],
                'AMD' => [
                    'symbol' => 'AMD',
                    'name' => 'Armenian Dram',
                    'symbol_native' => 'դր.',
                    'decimal_digits' => 0,
                    'code' => 'AMD',
                ],
                'ARS' => [
                    'symbol' => 'AR$',
                    'name' => 'Argentine Peso',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'code' => 'ARS',
                ],
                'AUD' => [
                    'symbol' => 'AU$',
                    'name' => 'Australian Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'code' => 'AUD',
                ],
                'AZN' => [
                    'symbol' => 'man.',
                    'name' => 'Azerbaijani Manat',
                    'symbol_native' => 'ман.',
                    'decimal_digits' => 2,
                    'code' => 'AZN',
                ],
                'BAM' => [
                    'symbol' => 'KM',
                    'name' => 'Bosnia-Herzegovina Convertible Mark',
                    'symbol_native' => 'KM',
                    'decimal_digits' => 2,
                    'code' => 'BAM',
                ],
                'BDT' => [
                    'symbol' => 'Tk',
                    'name' => 'Bangladeshi Taka',
                    'symbol_native' => '৳',
                    'decimal_digits' => 2,
                    'code' => 'BDT',
                ],
                'BGN' => [
                    'symbol' => 'BGN',
                    'name' => 'Bulgarian Lev',
                    'symbol_native' => 'лв.',
                    'decimal_digits' => 2,
                    'code' => 'BGN',
                ],
                'BHD' => [
                    'symbol' => 'BD',
                    'name' => 'Bahraini Dinar',
                    'symbol_native' => 'د.ب.‏',
                    'decimal_digits' => 3,
                    'code' => 'BHD',
                ],
                'BIF' => [
                    'symbol' => 'FBu',
                    'name' => 'Burundian Franc',
                    'symbol_native' => 'FBu',
                    'decimal_digits' => 0,
                    'code' => 'BIF',
                ],
                'BND' => [
                    'symbol' => 'BN$',
                    'name' => 'Brunei Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'code' => 'BND',
                ],
                'BOB' => [
                    'symbol' => 'Bs',
                    'name' => 'Bolivian Boliviano',
                    'symbol_native' => 'Bs',
                    'decimal_digits' => 2,
                    'code' => 'BOB',
                ],
                'BRL' => [
                    'symbol' => 'R$',
                    'name' => 'Brazilian Real',
                    'symbol_native' => 'R$',
                    'decimal_digits' => 2,
                    'code' => 'BRL',
                ],
                'BWP' => [
                    'symbol' => 'BWP',
                    'name' => 'Botswanan Pula',
                    'symbol_native' => 'P',
                    'decimal_digits' => 2,
                    'code' => 'BWP',
                ],
                'BYN' => [
                    'symbol' => 'Br',
                    'name' => 'Belarusian Ruble',
                    'symbol_native' => 'руб.',
                    'decimal_digits' => 2,
                    'code' => 'BYN',
                ],
                'BZD' => [
                    'symbol' => 'BZ$',
                    'name' => 'Belize Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'code' => 'BZD',
                ],
                'CDF' => [
                    'symbol' => 'CDF',
                    'name' => 'Congolese Franc',
                    'symbol_native' => 'FrCD',
                    'decimal_digits' => 2,
                    'code' => 'CDF',
                ],
                'CHF' => [
                    'symbol' => 'CHF',
                    'name' => 'Swiss Franc',
                    'symbol_native' => 'CHF',
                    'decimal_digits' => 2,
                    'code' => 'CHF',
                ],
                'CLP' => [
                    'symbol' => 'CL$',
                    'name' => 'Chilean Peso',
                    'symbol_native' => '$',
                    'decimal_digits' => 0,
                    'code' => 'CLP',
                ],
                'CNY' => [
                    'symbol' => 'CN¥',
                    'name' => 'Chinese Yuan',
                    'symbol_native' => 'CN¥',
                    'decimal_digits' => 2,
                    'code' => 'CNY',
                ],
                'COP' => [
                    'symbol' => 'CO$',
                    'name' => 'Colombian Peso',
                    'symbol_native' => '$',
                    'decimal_digits' => 0,
                    'code' => 'COP',
                ],
                'CRC' => [
                    'symbol' => '₡',
                    'name' => 'Costa Rican Colón',
                    'symbol_native' => '₡',
                    'decimal_digits' => 0,
                    'code' => 'CRC',
                ],
                'CVE' => [
                    'symbol' => 'CV$',
                    'name' => 'Cape Verdean Escudo',
                    'symbol_native' => 'CV$',
                    'decimal_digits' => 2,
                    'code' => 'CVE',
                ],
                'CZK' => [
                    'symbol' => 'Kč',
                    'name' => 'Czech Republic Koruna',
                    'symbol_native' => 'Kč',
                    'decimal_digits' => 2,
                    'code' => 'CZK',
                ],
                'DJF' => [
                    'symbol' => 'Fdj',
                    'name' => 'Djiboutian Franc',
                    'symbol_native' => 'Fdj',
                    'decimal_digits' => 0,
                    'code' => 'DJF',
                ],
                'DKK' => [
                    'symbol' => 'Dkr',
                    'name' => 'Danish Krone',
                    'symbol_native' => 'kr',
                    'decimal_digits' => 2,
                    'code' => 'DKK',
                ],
                'DOP' => [
                    'symbol' => 'RD$',
                    'name' => 'Dominican Peso',
                    'symbol_native' => 'RD$',
                    'decimal_digits' => 2,
                    'code' => 'DOP',
                ],
                'DZD' => [
                    'symbol' => 'DA',
                    'name' => 'Algerian Dinar',
                    'symbol_native' => 'د.ج.‏',
                    'decimal_digits' => 2,
                    'code' => 'DZD',
                ],
                'EEK' => [
                    'symbol' => 'Ekr',
                    'name' => 'Estonian Kroon',
                    'symbol_native' => 'kr',
                    'decimal_digits' => 2,
                    'code' => 'EEK',
                ],
                'EGP' => [
                    'symbol' => 'EGP',
                    'name' => 'Egyptian Pound',
                    'symbol_native' => 'ج.م.‏',
                    'decimal_digits' => 2,
                    'code' => 'EGP',
                ],
                'ERN' => [
                    'symbol' => 'Nfk',
                    'name' => 'Eritrean Nakfa',
                    'symbol_native' => 'Nfk',
                    'decimal_digits' => 2,
                    'code' => 'ERN',
                ],
                'ETB' => [
                    'symbol' => 'Br',
                    'name' => 'Ethiopian Birr',
                    'symbol_native' => 'Br',
                    'decimal_digits' => 2,
                    'code' => 'ETB',
                ],
                'GBP' => [
                    'symbol' => '£',
                    'name' => 'British Pound Sterling',
                    'symbol_native' => '£',
                    'decimal_digits' => 2,
                    'code' => 'GBP',
                ],
                'GEL' => [
                    'symbol' => 'GEL',
                    'name' => 'Georgian Lari',
                    'symbol_native' => 'GEL',
                    'decimal_digits' => 2,
                    'code' => 'GEL',
                ],
                'GHS' => [
                    'symbol' => 'GH₵',
                    'name' => 'Ghanaian Cedi',
                    'symbol_native' => 'GH₵',
                    'decimal_digits' => 2,
                    'code' => 'GHS',
                ],
                'GNF' => [
                    'symbol' => 'FG',
                    'name' => 'Guinean Franc',
                    'symbol_native' => 'FG',
                    'decimal_digits' => 0,
                    'code' => 'GNF',
                ],
                'GTQ' => [
                    'symbol' => 'GTQ',
                    'name' => 'Guatemalan Quetzal',
                    'symbol_native' => 'Q',
                    'decimal_digits' => 2,
                    'code' => 'GTQ',
                ],
                'HKD' => [
                    'symbol' => 'HK$',
                    'name' => 'Hong Kong Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'code' => 'HKD',
                ],
                'HNL' => [
                    'symbol' => 'HNL',
                    'name' => 'Honduran Lempira',
                    'symbol_native' => 'L',
                    'decimal_digits' => 2,
                    'code' => 'HNL',
                ],
                'HRK' => [
                    'symbol' => 'kn',
                    'name' => 'Croatian Kuna',
                    'symbol_native' => 'kn',
                    'decimal_digits' => 2,
                    'code' => 'HRK',
                ],
                'HUF' => [
                    'symbol' => 'Ft',
                    'name' => 'Hungarian Forint',
                    'symbol_native' => 'Ft',
                    'decimal_digits' => 0,
                    'code' => 'HUF',
                ],
                'IDR' => [
                    'symbol' => 'Rp',
                    'name' => 'Indonesian Rupiah',
                    'symbol_native' => 'Rp',
                    'decimal_digits' => 0,
                    'code' => 'IDR',
                ],
                'ILS' => [
                    'symbol' => '₪',
                    'name' => 'Israeli New Sheqel',
                    'symbol_native' => '₪',
                    'decimal_digits' => 2,
                    'code' => 'ILS',
                ],
                'INR' => [
                    'symbol' => 'Rs',
                    'name' => 'Indian Rupee',
                    'symbol_native' => 'টকা',
                    'decimal_digits' => 2,
                    'code' => 'INR',
                ],
                'IQD' => [
                    'symbol' => 'IQD',
                    'name' => 'Iraqi Dinar',
                    'symbol_native' => 'د.ع.‏',
                    'decimal_digits' => 0,
                    'code' => 'IQD',
                ],
                'IRR' => [
                    'symbol' => 'IRR',
                    'name' => 'Iranian Rial',
                    'symbol_native' => '﷼',
                    'decimal_digits' => 0,
                    'code' => 'IRR',
                ],
                'ISK' => [
                    'symbol' => 'Ikr',
                    'name' => 'Icelandic Króna',
                    'symbol_native' => 'kr',
                    'decimal_digits' => 0,
                    'code' => 'ISK',
                ],
                'JMD' => [
                    'symbol' => 'J$',
                    'name' => 'Jamaican Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'code' => 'JMD',
                ],
                'JOD' => [
                    'symbol' => 'JD',
                    'name' => 'Jordanian Dinar',
                    'symbol_native' => 'د.أ.‏',
                    'decimal_digits' => 3,
                    'code' => 'JOD',
                ],
                'JPY' => [
                    'symbol' => '¥',
                    'name' => 'Japanese Yen',
                    'symbol_native' => '￥',
                    'decimal_digits' => 0,
                    'code' => 'JPY',
                ],
                'KES' => [
                    'symbol' => 'Ksh',
                    'name' => 'Kenyan Shilling',
                    'symbol_native' => 'Ksh',
                    'decimal_digits' => 2,
                    'code' => 'KES',
                ],
                'KHR' => [
                    'symbol' => 'KHR',
                    'name' => 'Cambodian Riel',
                    'symbol_native' => '៛',
                    'decimal_digits' => 2,
                    'code' => 'KHR',
                ],
                'KMF' => [
                    'symbol' => 'CF',
                    'name' => 'Comorian Franc',
                    'symbol_native' => 'FC',
                    'decimal_digits' => 0,
                    'code' => 'KMF',
                ],
                'KRW' => [
                    'symbol' => '₩',
                    'name' => 'South Korean Won',
                    'symbol_native' => '₩',
                    'decimal_digits' => 0,
                    'code' => 'KRW',
                ],
                'KWD' => [
                    'symbol' => 'KD',
                    'name' => 'Kuwaiti Dinar',
                    'symbol_native' => 'د.ك.‏',
                    'decimal_digits' => 3,
                    'code' => 'KWD',
                ],
                'KZT' => [
                    'symbol' => 'KZT',
                    'name' => 'Kazakhstani Tenge',
                    'symbol_native' => 'тңг.',
                    'decimal_digits' => 2,
                    'code' => 'KZT',
                ],
                'LBP' => [
                    'symbol' => 'L.L.',
                    'name' => 'Lebanese Pound',
                    'symbol_native' => 'ل.ل.‏',
                    'decimal_digits' => 0,
                    'code' => 'LBP',
                ],
                'LKR' => [
                    'symbol' => 'SLRs',
                    'name' => 'Sri Lankan Rupee',
                    'symbol_native' => 'SL Re',
                    'decimal_digits' => 2,
                    'code' => 'LKR',
                ],
                'LTL' => [
                    'symbol' => 'Lt',
                    'name' => 'Lithuanian Litas',
                    'symbol_native' => 'Lt',
                    'decimal_digits' => 2,
                    'code' => 'LTL',
                ],
                'LVL' => [
                    'symbol' => 'Ls',
                    'name' => 'Latvian Lats',
                    'symbol_native' => 'Ls',
                    'decimal_digits' => 2,
                    'code' => 'LVL',
                ],
                'LYD' => [
                    'symbol' => 'LD',
                    'name' => 'Libyan Dinar',
                    'symbol_native' => 'د.ل.‏',
                    'decimal_digits' => 3,
                    'code' => 'LYD',
                ],
                'MAD' => [
                    'symbol' => 'MAD',
                    'name' => 'Moroccan Dirham',
                    'symbol_native' => 'د.م.‏',
                    'decimal_digits' => 2,
                    'code' => 'MAD',
                ],
                'MDL' => [
                    'symbol' => 'MDL',
                    'name' => 'Moldovan Leu',
                    'symbol_native' => 'MDL',
                    'decimal_digits' => 2,
                    'code' => 'MDL',
                ],
                'MGA' => [
                    'symbol' => 'MGA',
                    'name' => 'Malagasy Ariary',
                    'symbol_native' => 'MGA',
                    'decimal_digits' => 0,
                    'code' => 'MGA',
                ],
                'MKD' => [
                    'symbol' => 'MKD',
                    'name' => 'Macedonian Denar',
                    'symbol_native' => 'MKD',
                    'decimal_digits' => 2,
                    'code' => 'MKD',
                ],
                'MMK' => [
                    'symbol' => 'MMK',
                    'name' => 'Myanma Kyat',
                    'symbol_native' => 'K',
                    'decimal_digits' => 0,
                    'code' => 'MMK',
                ],
                'MOP' => [
                    'symbol' => 'MOP$',
                    'name' => 'Macanese Pataca',
                    'symbol_native' => 'MOP$',
                    'decimal_digits' => 2,
                    'code' => 'MOP',
                ],
                'MUR' => [
                    'symbol' => 'MURs',
                    'name' => 'Mauritian Rupee',
                    'symbol_native' => 'MURs',
                    'decimal_digits' => 0,
                    'code' => 'MUR',
                ],
                'MXN' => [
                    'symbol' => 'MX$',
                    'name' => 'Mexican Peso',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'code' => 'MXN',
                ],
                'MYR' => [
                    'symbol' => 'RM',
                    'name' => 'Malaysian Ringgit',
                    'symbol_native' => 'RM',
                    'decimal_digits' => 2,
                    'code' => 'MYR',
                ],
                'MZN' => [
                    'symbol' => 'MTn',
                    'name' => 'Mozambican Metical',
                    'symbol_native' => 'MTn',
                    'decimal_digits' => 2,
                    'code' => 'MZN',
                ],
                'NAD' => [
                    'symbol' => 'N$',
                    'name' => 'Namibian Dollar',
                    'symbol_native' => 'N$',
                    'decimal_digits' => 2,
                    'code' => 'NAD',
                ],
                'NGN' => [
                    'symbol' => '₦',
                    'name' => 'Nigerian Naira',
                    'symbol_native' => '₦',
                    'decimal_digits' => 2,
                    'code' => 'NGN',
                ],
                'NIO' => [
                    'symbol' => 'C$',
                    'name' => 'Nicaraguan Córdoba',
                    'symbol_native' => 'C$',
                    'decimal_digits' => 2,
                    'code' => 'NIO',
                ],
                'NOK' => [
                    'symbol' => 'Nkr',
                    'name' => 'Norwegian Krone',
                    'symbol_native' => 'kr',
                    'decimal_digits' => 2,
                    'code' => 'NOK',
                ],
                'NPR' => [
                    'symbol' => 'NPRs',
                    'name' => 'Nepalese Rupee',
                    'symbol_native' => 'नेरू',
                    'decimal_digits' => 2,
                    'code' => 'NPR',
                ],
                'NZD' => [
                    'symbol' => 'NZ$',
                    'name' => 'New Zealand Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'code' => 'NZD',
                ],
                'OMR' => [
                    'symbol' => 'OMR',
                    'name' => 'Omani Rial',
                    'symbol_native' => 'ر.ع.‏',
                    'decimal_digits' => 3,
                    'code' => 'OMR',
                ],
                'PAB' => [
                    'symbol' => 'B/.',
                    'name' => 'Panamanian Balboa',
                    'symbol_native' => 'B/.',
                    'decimal_digits' => 2,
                    'code' => 'PAB',
                ],
                'PEN' => [
                    'symbol' => 'S/.',
                    'name' => 'Peruvian Nuevo Sol',
                    'symbol_native' => 'S/.',
                    'decimal_digits' => 2,
                    'code' => 'PEN',
                ],
                'PHP' => [
                    'symbol' => '₱',
                    'name' => 'Philippine Peso',
                    'symbol_native' => '₱',
                    'decimal_digits' => 2,
                    'code' => 'PHP',
                ],
                'PKR' => [
                    'symbol' => 'PKRs',
                    'name' => 'Pakistani Rupee',
                    'symbol_native' => '₨',
                    'decimal_digits' => 0,
                    'code' => 'PKR',
                ],
                'PLN' => [
                    'symbol' => 'zł',
                    'name' => 'Polish Zloty',
                    'symbol_native' => 'zł',
                    'decimal_digits' => 2,
                    'code' => 'PLN',
                ],
                'PYG' => [
                    'symbol' => '₲',
                    'name' => 'Paraguayan Guarani',
                    'symbol_native' => '₲',
                    'decimal_digits' => 0,
                    'code' => 'PYG',
                ],
                'QAR' => [
                    'symbol' => 'QR',
                    'name' => 'Qatari Rial',
                    'symbol_native' => 'ر.ق.‏',
                    'decimal_digits' => 2,
                    'code' => 'QAR',
                ],
                'RON' => [
                    'symbol' => 'RON',
                    'name' => 'Romanian Leu',
                    'symbol_native' => 'RON',
                    'decimal_digits' => 2,
                    'code' => 'RON',
                ],
                'RSD' => [
                    'symbol' => 'din.',
                    'name' => 'Serbian Dinar',
                    'symbol_native' => 'дин.',
                    'decimal_digits' => 0,
                    'code' => 'RSD',
                ],
                'RUB' => [
                    'symbol' => 'RUB',
                    'name' => 'Russian Ruble',
                    'symbol_native' => '₽.',
                    'decimal_digits' => 2,
                    'code' => 'RUB',
                ],
                'RWF' => [
                    'symbol' => 'RWF',
                    'name' => 'Rwandan Franc',
                    'symbol_native' => 'FR',
                    'decimal_digits' => 0,
                    'code' => 'RWF',
                ],
                'SAR' => [
                    'symbol' => 'SR',
                    'name' => 'Saudi Riyal',
                    'symbol_native' => 'ر.س.‏',
                    'decimal_digits' => 2,
                    'code' => 'SAR',
                ],
                'SDG' => [
                    'symbol' => 'SDG',
                    'name' => 'Sudanese Pound',
                    'symbol_native' => 'SDG',
                    'decimal_digits' => 2,
                    'code' => 'SDG',
                ],
                'SEK' => [
                    'symbol' => 'Skr',
                    'name' => 'Swedish Krona',
                    'symbol_native' => 'kr',
                    'decimal_digits' => 2,
                    'code' => 'SEK',
                ],
                'SGD' => [
                    'symbol' => 'S$',
                    'name' => 'Singapore Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'code' => 'SGD',
                ],
                'SOS' => [
                    'symbol' => 'Ssh',
                    'name' => 'Somali Shilling',
                    'symbol_native' => 'Ssh',
                    'decimal_digits' => 0,
                    'code' => 'SOS',
                ],
                'SYP' => [
                    'symbol' => 'SY£',
                    'name' => 'Syrian Pound',
                    'symbol_native' => 'ل.س.‏',
                    'decimal_digits' => 0,
                    'code' => 'SYP',
                ],
                'THB' => [
                    'symbol' => '฿',
                    'name' => 'Thai Baht',
                    'symbol_native' => '฿',
                    'decimal_digits' => 2,
                    'code' => 'THB',
                ],
                'TND' => [
                    'symbol' => 'DT',
                    'name' => 'Tunisian Dinar',
                    'symbol_native' => 'د.ت.‏',
                    'decimal_digits' => 3,
                    'code' => 'TND',
                ],
                'TOP' => [
                    'symbol' => 'T$',
                    'name' => 'Tongan Paʻanga',
                    'symbol_native' => 'T$',
                    'decimal_digits' => 2,
                    'code' => 'TOP',
                ],
                'TRY' => [
                    'symbol' => 'TL',
                    'name' => 'Turkish Lira',
                    'symbol_native' => 'TL',
                    'decimal_digits' => 2,
                    'code' => 'TRY',
                ],
                'TTD' => [
                    'symbol' => 'TT$',
                    'name' => 'Trinidad and Tobago Dollar',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'code' => 'TTD',
                ],
                'TWD' => [
                    'symbol' => 'NT$',
                    'name' => 'New Taiwan Dollar',
                    'symbol_native' => 'NT$',
                    'decimal_digits' => 2,
                    'code' => 'TWD',
                ],
                'TZS' => [
                    'symbol' => 'TSh',
                    'name' => 'Tanzanian Shilling',
                    'symbol_native' => 'TSh',
                    'decimal_digits' => 0,
                    'code' => 'TZS',
                ],
                'UAH' => [
                    'symbol' => '₴',
                    'name' => 'Ukrainian Hryvnia',
                    'symbol_native' => '₴',
                    'decimal_digits' => 2,
                    'code' => 'UAH',
                ],
                'UGX' => [
                    'symbol' => 'USh',
                    'name' => 'Ugandan Shilling',
                    'symbol_native' => 'USh',
                    'decimal_digits' => 0,
                    'code' => 'UGX',
                ],
                'UYU' => [
                    'symbol' => '$U',
                    'name' => 'Uruguayan Peso',
                    'symbol_native' => '$',
                    'decimal_digits' => 2,
                    'code' => 'UYU',
                ],
                'UZS' => [
                    'symbol' => 'UZS',
                    'name' => 'Uzbekistan Som',
                    'symbol_native' => 'UZS',
                    'decimal_digits' => 0,
                    'code' => 'UZS',
                ],
                'VEF' => [
                    'symbol' => 'Bs.F.',
                    'name' => 'Venezuelan Bolívar',
                    'symbol_native' => 'Bs.F.',
                    'decimal_digits' => 2,
                    'code' => 'VEF',
                ],
                'VND' => [
                    'symbol' => '₫',
                    'name' => 'Vietnamese Dong',
                    'symbol_native' => '₫',
                    'decimal_digits' => 0,
                    'code' => 'VND',
                ],
                'XAF' => [
                    'symbol' => 'FCFA',
                    'name' => 'CFA Franc BEAC',
                    'symbol_native' => 'FCFA',
                    'decimal_digits' => 0,
                    'code' => 'XAF',
                ],
                'XOF' => [
                    'symbol' => 'CFA',
                    'name' => 'CFA Franc BCEAO',
                    'symbol_native' => 'CFA',
                    'decimal_digits' => 0,
                    'code' => 'XOF',
                ],
                'YER' => [
                    'symbol' => 'YR',
                    'name' => 'Yemeni Rial',
                    'symbol_native' => 'ر.ي.‏',
                    'decimal_digits' => 0,
                    'code' => 'YER',
                ],
                'ZAR' => [
                    'symbol' => 'R',
                    'name' => 'South African Rand',
                    'symbol_native' => 'R',
                    'decimal_digits' => 2,
                    'code' => 'ZAR',
                ],
                'ZMK' => [
                    'symbol' => 'ZK',
                    'name' => 'Zambian Kwacha',
                    'symbol_native' => 'ZK',
                    'decimal_digits' => 0,
                    'code' => 'ZMK',
                ],
                'ZWL' => [
                    'symbol' => 'ZWL$',
                    'name' => 'Zimbabwean Dollar',
                    'symbol_native' => 'ZWL$',
                    'decimal_digits' => 0,
                    'code' => 'ZWL',
                ],
            ];
        });
    }

    /**
     * Find currency matching the given currency code.
     *
     * @param string $code e.g USD
     * @return array|null
     */
    public static function findCurrencyByCode(string $code): ?array
    {
        return (new CacheService(CacheName::CURRENCIES))->append($code)->remember(now()->addDay(), function () use ($code) {
            $currencies = self::getCurrencies();
            return $currencies[strtoupper($code)] ?? null;
        });
    }

    /**
     * Convert amount from one currency code to another.
     *
     * @param float $amount e.g 100.00
     * @param string $from e.g BWP
     * @param string $to e.g USD
     * @return array|null
     * @throws Exception
     */
    public static function convertCurrency(float $amount, string $from, string $to): array
    {
        $from = strtoupper($from);
        $to = strtoupper($to);
        $intermediary = 'USD';

        $rate = 1;
        $successful = true;
        $usedFallback = false;
        $message = 'Conversion successful.';

        if ($from !== $to) {

            $cacheManager = (new CacheService(CacheName::CONVERTED_CURRENCY))->append($from)->append($to);

            try {

                $rate = $cacheManager->remember(now()->addHours(12), function () use ($from, $to, $intermediary, &$usedFallback) {
                    $directRate = self::fetchRate($from, $to);

                    if ($directRate !== null) {
                        return $directRate;
                    }

                    // Fallback via intermediary
                    $rateFromToIntermediary = self::fetchRate($from, $intermediary);
                    $rateIntermediaryToTo = self::fetchRate($intermediary, $to);

                    if ($rateFromToIntermediary !== null && $rateIntermediaryToTo !== null) {
                        $usedFallback = true;
                        return $rateFromToIntermediary * $rateIntermediaryToTo;
                    }

                    throw new Exception("Unable to convert from $from to $to, even via $intermediary.");

                });

            } catch (Exception $e) {

                $successful = false;
                $message = "Currency conversion failed: " . $e->getMessage();

            }
        }

        $toCurrency = self::findCurrencyByCode($to);
        $convertedAmount = round($amount * $rate, $toCurrency['decimal_digits'] ?? 2);

        $fromMoney = self::convertToMoneyFormat($amount, $from);
        $toMoney = self::convertToMoneyFormat($convertedAmount, $to);

        return [
            'rate' => $rate,
            'to' => $toMoney,
            'from' => $fromMoney,
            'message' => $message,
            'successful' => $successful,
            'usedFallback' => $usedFallback,
        ];
    }

    /**
     * Get conversation rate.
     *
     * @param float $amount e.g 100.00
     * @param string $from e.g BWP
     * @param string $to e.g USD
     * @return ?float
     */
    private static function fetchRate(string $from, string $to): ?float
    {
        $symbol = "{$from}/{$to}";
        $apiKey = config('app.fcs_api_key');

        try {

            $response = Http::get('https://fcsapi.com/api-v3/forex/latest', [
                'symbol' => $symbol,
                'access_key' => $apiKey,
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? false) === true) {
                if (isset($data['response'][0]['c'])) {
                    return (float) $data['response'][0]['c'];
                }
            }

            Log::warning("Failed to fetch direct rate for $symbol", [
                'response' => $data
            ]);

        } catch (\Throwable $e) {

            Log::error("API exception while fetching rate for $symbol", [
                'error' => $e->getMessage()
            ]);

        }

        return null;
    }

    /**
     * Convert to money format.
     *
     * @param float $amount e.g 100.00
     * @param string $code e.g BWP
     * @return stdClass
     * @throws Exception
     */
    public static function convertToMoneyFormat($amount = 0, $code = null): stdClass
    {
        try {

            $currency = self::findCurrencyByCode($code);
            $decimal = $currency['decimal_digits'];
            $symbol = $currency['symbol_native'];

            // Format with commas for display
            $money = number_format($amount, $decimal, '.', ',');

            // Format without commas, fixed decimal places (string)
            $formattedAmount = number_format($amount, $decimal, '.', '');

            $obj = new stdClass();
            $obj->amount = $formattedAmount;
            $obj->amount_without_currency = $money;
            $obj->amount_with_currency = $symbol . $money;

            return $obj;

        } catch (Exception $e) {

            throw new Exception('Failed to convert to money format: ' . $e->getMessage());

        }
    }

    /**
     * Convert to currency format.
     *
     * @param string $code e.g BWP
     * @return stdClass
     * @throws Exception
     */
    public static function convertToCurrencyFormat($code): stdClass
    {
        try {

            $currency = MoneyService::findCurrencyByCode($code);

            $obj = new stdClass();
            $obj->code = strtoupper($currency['code']);
            $obj->symbol = $currency['symbol_native'];

            return $obj;

        } catch (Exception $e) {

            throw new Exception('Failed to convert to currency format: ' . $e->getMessage());

        }
    }
}
