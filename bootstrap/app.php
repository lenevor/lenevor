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

require __DIR__.'/../syscode/requeriments.php';

/*
|---------------------------------------------------------------------------
| REGISTER BOOTSTRAP CORE
|---------------------------------------------------------------------------
|
| Load bootstrap from the core of system. 
|
*/

require __DIR__.'/../syscode/bootstrap.php';

// Create the application
$app = new Syscode\Core\Application(
    dirname(__DIR__)
);

$app->singleton(
    Syscode\Contracts\Core\Lenevor::class, 
    Syscode\Core\Http\Lenevor::class
);

$app->singleton(
    Syscode\Contracts\Console\Application::class, 
    App\Console\Application::class
);

$app->singleton(
    Syscode\Contracts\Debug\Handler::class, 
    App\Exceptions\Handler::class
);

return $app;