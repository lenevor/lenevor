<?php 

use Syscodes\Components\Core\Application;
use Syscodes\Components\Core\Configuration\ExceptionBootstrap;
use Syscodes\Components\Core\Configuration\MiddlewareBootstrap;
use Syscodes\Components\Http\Request;

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
        commands: __DIR__.'/../routes/console.php',
    )
    ->assignMiddleware(function (MiddlewareBootstrap $middleware): void {
        //
    })
    ->assignExceptions(function (ExceptionBootstrap $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create(); // Return the application