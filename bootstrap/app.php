<?php 

define('LENEVOR_START', microtime(true));

/*
|---------------------------------------------------------------------------
| CHECK PLATAFORM REQUIREMENTS
|---------------------------------------------------------------------------
|
| This verification allows to know the version of the system and other 
| resources that PHP uses the framework.
|
*/

require __DIR__.'/../syscodes/src/requeriments.php';

/*
|---------------------------------------------------------------------------
| REGISTER BOOTSTRAP CORE
|---------------------------------------------------------------------------
|
| Load bootstrap from the core of system. 
|
*/

require __DIR__.'/../syscodes/src/bootstrap.php';

// Create the application
$app = new Syscodes\Core\Application(
    dirname(__DIR__)
);

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

return $app;