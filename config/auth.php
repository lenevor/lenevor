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
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
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
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],
    ],

    /*
    |------------------------------------------------------------------------
    | Resetting Password
    |------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more 
    | than one user table or model in your application, and you can also have 
    | separate password reset settings based on specific user types.
    |
    | The expire time is the number of minutes that each reset token will be 
    | considered valid. This security feature keeps tokens short-lived, so they
    | have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_resets_tokens'),
            'expires' => 60,
            'throttle' => 60,
        ],
    ],

];