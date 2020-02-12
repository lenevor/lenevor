<?php

return [

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
        realpath(storagePath('views'))
    ),

];