<?php

return [

    'fcs_api_key' => env('FCS_API_KEY'),
    'country' => env('DEFAULT_COUNTRY', 'bw'),
    'language' => env('DEFAULT_LANGUAGE', 'en'),
    'currency' => env('DEFAULT_CURRENCY', 'bwp'),

    'orange_sms_url' => env('ORANGE_SMS_URL'),
    'orange_sms_enabled' => env('ORANGE_SMS_ENABLED'),
    'orange_sms_sender_name' => env('ORANGE_SMS_SENDER_NAME'),
    'orange_sms_credentials' => env('ORANGE_SMS_CREDENTIALS'),
    'orange_sms_sender_mobile_number' => env('ORANGE_SMS_SENDER_MOBILE_NUMBER'),

    'orange_airtime_billing_url' => env('ORANGE_AIRTIME_BILLING_URL'),
    'orange_airtime_billing_enabled' => env('ORANGE_AIRTIME_BILLING_ENABLED'),
    'orange_airtime_billing_client_id' => env('ORANGE_AIRTIME_BILLING_CLIENT_ID'),
    'orange_airtime_billing_on_behalf_of' => env('ORANGE_AIRTIME_BILLING_ON_BEHALF_OF'),
    'orange_airtime_billing_client_secret' => env('ORANGE_AIRTIME_BILLING_CLIENT_SECRET'),

    'ussd_token' => env('USSD_TOKEN'),

    'dpo_company_token' => env('DPO_COMPANY_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
