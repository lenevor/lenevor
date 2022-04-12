<?php

use Syscodes\Components\Support\Facades\Facade;

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

    'providers' => [

        /*
         * Lenevor Framework Service Providers
         */
        Syscodes\Components\Cache\CacheServiceProvider::class,
        Syscodes\Components\Database\DatabaseServiceProvider::class,
        Syscodes\Components\Encryption\EncryptionServiceProvider::class,
        Syscodes\Components\Events\EventServiceProvider::class,
        Syscodes\Components\Filesystem\FilesystemServiceProvider::class,
        Syscodes\Components\Pipeline\PipelineServiceProvider::class,
        Syscodes\Components\Session\SessionServiceProvider::class,
        Syscodes\Components\Translation\TranslationServiceProvider::class,
        Syscodes\Components\View\ViewServiceProvider::class,


        /**
         * Application Service Providers
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

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