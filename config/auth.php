<?php

return [

    /*
    |------------------------------------------------------------------------
    | Authentication Defaults
    |------------------------------------------------------------------------
    |
    | This option manages the default authentication "guard" and password 
    | reset options for your application. Also, you may change these default 
    | values as required, however, it is a perfect start for most applications.
    |
    */

    'defaults' => [ 
        'guard' => 'web'
    ],

    /*
    |------------------------------------------------------------------------
    | Authentication Guards
    |------------------------------------------------------------------------
    |
    | This option defines each authentication guard for your application, the 
    | session storage and the Erostrine user provider are used.
    |
    | All authentication drivers have a user provider, which users are 
    | retrieved from your database or other storage to hold your user data.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |------------------------------------------------------------------------
    | User Providers
    |------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. Here it is defined how 
    | users are retrieved from your database or other storage mechanism used 
    | by this application in order to persist your user's data.
    | 
    | If you have multiple user tables or models you may configure each 
    | authentication guards by adding them to each model/table you have defined.
    |
    | Supported: "database", "erostrine"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'erostrine',
            'model' => App\Model\User::class,
        ],
    ],

];