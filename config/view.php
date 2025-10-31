<?php

return [

    /*
    |------------------------------------------------------------------------
    | View Storage Paths
    |------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specif 
    | an array of paths that should be checked for your views. Of course the 
    | usual Lenevor view path has already been registered for you. 
    |
    */

    'paths' => [
        resource_path('views')
    ],

    /*
    |------------------------------------------------------------------------
    | Transpiled View Paths
    |------------------------------------------------------------------------
    |
    | This option determines where the transpiled Plaze templates will be. 
    | However, the directory may vary if you wish.  
    |
    */

    'transpiled' => env(
        'TRANSPILED_VIEW_PATH', 
        realpath(storage_path('framework/views'))
    ),

];