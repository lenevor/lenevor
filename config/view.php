<?php

return [

    /*
    |------------------------------------------------------------------------
    | Compiled View Paths
    |------------------------------------------------------------------------
    |
    | This option determines where the compiled Plaze templates will be. 
    | However, the directory may vary if you wish.  
    |
    */

    'compiled' => env(
        'COMPILED_VIEW_PATH', 
        realpath(storagePath('views'))
    ),

];