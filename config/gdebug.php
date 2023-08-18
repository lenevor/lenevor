<?php

return [

    /*
    |------------------------------------------------------------------------
    | Default Register Editor                                                   
    |------------------------------------------------------------------------
    |
    | This determines the editor register with the handler assigned by the user, 
    | those are as follows:
    |
    | Supported: "vscode", "sublime", "phpstorm", "textmate"
    |
    */

    'editor' => env('GDEBUG_EDITOR', 'vscode'),

    /*
    |------------------------------------------------------------------------
    | Default Theme
    |------------------------------------------------------------------------
    |
    | Here you may specify which theme GDebug should use.
    |
    | Supported: "light", "dark", "auto"
    |
    */

    'theme' => env('GDEBUG_THEME', 'auto'),

];