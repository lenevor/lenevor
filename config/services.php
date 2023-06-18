<?php

use Syscodes\Components\Support\Facades\Facade;
use Syscodes\Components\Support\ServiceProvider;

return [
    
    /*
    |------------------------------------------------------------------------
    | Autoloaded Service Providers
    |------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded. You can 
    | add your own services to this array to grant expanded functionality to 
    | your applications.
    |
    */

    'providers' => ServiceProvider::defaultCoreProviders()->merge([
        // Package Services Providers...

        // Application Service Providers...
        \App\Providers\AppServiceProvider::class,
        \App\Providers\EventServiceProvider::class,
        \App\Providers\RouteServiceProvider::class,
    ])->toArray(),

    /*
    |------------------------------------------------------------------------
    | Class Aliases
    |------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when an application is 
    | started. You can added the aliases loaded so they don't hinder performance. 
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        // 'ExampleClass' => App\Example\ExampleClass::class,
    ])->toArray(),

];