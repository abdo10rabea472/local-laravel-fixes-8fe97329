<?php

/**
 * Aramex SDK Configuration (octw/aramex)
 *
 * The SDK reads:
 *   config('aramex.ENV')                 -> 'TEST' or 'LIVE'
 *   config('aramex.'.ENV)['UserName']    -> credentials block keyed by env name
 *   config('aramex.LabelInfo')           -> label settings
 *   config('aramex.company_name')        -> shipper company name
 */

$clientInfo = [
    'UserName'           => env('ARAMEX_USERNAME', 'testingapi@aramex.com'),
    'Password'           => env('ARAMEX_PASSWORD', 'R123456789$r'),
    'Version'            => env('ARAMEX_VERSION', 'v1.0'),
    'AccountNumber'      => env('ARAMEX_ACCOUNT_NUMBER', '20016'),
    'AccountPin'         => env('ARAMEX_ACCOUNT_PIN', '331421'),
    'AccountEntity'      => env('ARAMEX_ACCOUNT_ENTITY', 'AMM'),
    'AccountCountryCode' => env('ARAMEX_COUNTRY_CODE', 'JO'),
    'Source'             => env('ARAMEX_SOURCE', 24),
];

return [

    // SDK requires this exact key. Must be 'TEST' or 'LIVE'.
    'ENV' => env('ARAMEX_ENVIRONMENT', 'TEST'),

    // Credentials block per environment — the SDK looks up config('aramex.'.ENV)
    'TEST' => $clientInfo,
    'LIVE' => $clientInfo,

    // Back-compat aliases (used by our own AramexService::isConfigured check)
    'testing_mode' => env('ARAMEX_TESTING_MODE', true),
    'ClientInfo'   => $clientInfo,

    'company_name' => env('ARAMEX_SHIPPER_COMPANY', 'Uni Lab Store'),

    'LabelInfo' => [
        'ReportID'   => 9201,
        'ReportType' => 'URL',
    ],

    'Shipper' => [
        'Reference1'    => env('ARAMEX_SHIPPER_REF', 'UNI-LAB'),
        'AccountNumber' => env('ARAMEX_ACCOUNT_NUMBER', '20016'),
        'PartyAddress'  => [
            'Line1'               => env('ARAMEX_SHIPPER_LINE1', 'Cairo HQ'),
            'Line2'               => env('ARAMEX_SHIPPER_LINE2', ''),
            'Line3'               => env('ARAMEX_SHIPPER_LINE3', ''),
            'City'                => env('ARAMEX_SHIPPER_CITY', 'Cairo'),
            'StateOrProvinceCode' => env('ARAMEX_SHIPPER_STATE', ''),
            'PostCode'            => env('ARAMEX_SHIPPER_POSTCODE', '00000'),
            'CountryCode'         => env('ARAMEX_SHIPPER_COUNTRY', 'EG'),
        ],
        'Contact' => [
            'PersonName'   => env('ARAMEX_SHIPPER_NAME', 'Uni Lab'),
            'CompanyName'  => env('ARAMEX_SHIPPER_COMPANY', 'Uni Lab Store'),
            'PhoneNumber1' => env('ARAMEX_SHIPPER_PHONE', '+201000000000'),
            'CellPhone'    => env('ARAMEX_SHIPPER_CELL', '+201000000000'),
            'EmailAddress' => env('ARAMEX_SHIPPER_EMAIL', 'store@example.com'),
            'Type'         => '',
        ],
    ],
];
