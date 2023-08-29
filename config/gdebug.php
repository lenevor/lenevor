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
    | Supported: "sublime", "emacs", "textmate", "macvim", "phpstorm", "idea",
    |            "netbeans", "vscode", "atom"
    |
    */

    'editor' => env('GDEBUG_EDITOR', 'phpstorm'),

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