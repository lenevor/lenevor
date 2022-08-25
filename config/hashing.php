<?php

return [

    /*
    |------------------------------------------------------------------------
    | Default Hash Driver                                                       
    |------------------------------------------------------------------------
    |
    | This option allows you to have the default hash driver that will be used 
    | hash passwords for your application. The bcrypt algorithm will be used 
    | by default; however, you remain free to modify this option if you wish, 
    | as long as it is supported in the framework.
    |
    | Suported: "bcrypt"
    |
    */

    'driver' => 'bcrypt',

    /*
    |------------------------------------------------------------------------
    | Bcrypt Options                                                      
    |------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Bcrypt algorithm. This will allow you
    | to control the amount of time it takes to hash the given password.
    |
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 10),
    ],

];