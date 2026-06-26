<?php

/**
 * Aramex SDK Configuration
 * Values are pulled from .env so credentials never live in source control.
 * Defaults below use the official Aramex Sandbox/Test credentials so you can
 * verify the integration immediately. Replace them with your live account
 * details in .env for production.
 */
return [

    'testing_mode' => env('ARAMEX_TESTING_MODE', true),

    'ClientInfo' => [
        'UserName'           => env('ARAMEX_USERNAME', 'testingapi@aramex.com'),
        'Password'           => env('ARAMEX_PASSWORD', 'R123456789$r'),
        'Version'            => env('ARAMEX_VERSION', 'v1.0'),
        'AccountNumber'      => env('ARAMEX_ACCOUNT_NUMBER', '20016'),
        'AccountPin'         => env('ARAMEX_ACCOUNT_PIN', '331421'),
        'AccountEntity'      => env('ARAMEX_ACCOUNT_ENTITY', 'AMM'),
        'AccountCountryCode' => env('ARAMEX_COUNTRY_CODE', 'JO'),
        'Source'             => env('ARAMEX_SOURCE', 24),
    ],

    'Shipper' => [
        'Reference1'   => env('ARAMEX_SHIPPER_REF', 'UNI-LAB'),
        'AccountNumber'=> env('ARAMEX_ACCOUNT_NUMBER', '20016'),
        'PartyAddress' => [
            'Line1'       => env('ARAMEX_SHIPPER_LINE1', 'Cairo HQ'),
            'Line2'       => env('ARAMEX_SHIPPER_LINE2', ''),
            'Line3'       => env('ARAMEX_SHIPPER_LINE3', ''),
            'City'        => env('ARAMEX_SHIPPER_CITY', 'Cairo'),
            'StateOrProvinceCode' => env('ARAMEX_SHIPPER_STATE', ''),
            'PostCode'    => env('ARAMEX_SHIPPER_POSTCODE', '00000'),
            'CountryCode' => env('ARAMEX_SHIPPER_COUNTRY', 'EG'),
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
