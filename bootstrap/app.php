<?php 

use Syscodes\Components\Core\Application;
use Syscodes\Components\Core\Configuration\ExceptionBootstrap;
use Syscodes\Components\Core\Configuration\MiddlewareBootstrap;

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

    ->assignRouting(
        web: __DIR__.'/../routes/web.php',
    )
    ->assignMiddlewares(function (MiddlewareBootstrap $middleware) {
        //
    })
    ->assignExceptions(function (ExceptionBootstrap $exceptions) {
        //
    })->create(); // Return the application