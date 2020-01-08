<?php

return [

    /*
    |-----------------------------------------------------------------------
    | Security X Headers
    |-----------------------------------------------------------------------
    |
    | Allow the Input class to use X headers when present.
    |
    */

    'allow-x-headers' => false,
    
    /*
    |-----------------------------------------------------------------------
    |  Security CSRF Activate
    |-----------------------------------------------------------------------
    |
    |  Prevents the website from CSRF attacks.
    */
    
    'csrf' => true,

    /*
    |-----------------------------------------------------------------------
    | Encryption Key
    |-----------------------------------------------------------------------
    |
    | This key is used by the syscode encrypter service and should be set 
    | to a random, 32 character string, otherwise these encrypted strings 
    | will not be safe. Please do this before deploying an application! 
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

];