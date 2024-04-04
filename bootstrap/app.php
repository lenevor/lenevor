<?php 

use Syscodes\Components\Core\Application;

return Application::configure(basePath: dirname(__DIR__))
    /*
    |------------------------------------------------------------------------
    | Bind Important Interfaces
    |------------------------------------------------------------------------
    |
    | Next, we need to bind some important interfaces into the container for 
    | be will be able to resolve the incomming requests to this application 
    | from both the web and CLI.
    |
    */
    ->assignExceptions(function () {
        //
    })->create(); // Return the application