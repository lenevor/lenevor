<?php 

/*
|------------------------------------------------------------------------
| Create the application
|------------------------------------------------------------------------
|
| What we will do is create a new instance of a Lenevor application that 
| serves to request all the Lenevor components and is the IoC container 
| for the system that joins all the parts of the framework.
|
*/

$app = new Syscodes\Core\Application(
    $_ENV['APP_ROOT_PATH'] ?? dirname(__DIR__)
);

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

$app->singleton(
    Syscodes\Contracts\Core\Lenevor::class, 
    App\Http\Lenevor::class
);

$app->singleton(
    Syscodes\Contracts\Console\Lenevor::class, 
    App\Console\Lenevor::class
);

$app->singleton(
    Syscodes\Contracts\Debug\ExceptionHandler::class, 
    App\Exceptions\Handler::class
);

/*
|------------------------------------------------------------------------
| Return The Application
|------------------------------------------------------------------------
|
| This variable returns the application instance for the building of 
| instances from the running of the application and sending responses.
|
*/

return $app;