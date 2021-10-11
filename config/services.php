<?php

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
    | This array of class aliases will be registered cuando an application is 
    | started. You can added the aliases loaded so they don't hinder performance. 
    |
    */

    'aliases' => [

        'App' => Syscodes\Components\Support\Facades\App::class,
        'Cache' => Syscodes\Components\Support\Facades\Cache::class,
        'Config' => Syscodes\Components\Support\Facades\Config::class,
        'Crypt' => Syscodes\Components\Support\Facades\Crypt::class,
        'DB' => Syscodes\Components\Support\Facades\DB::class,
        'Event' => Syscodes\Components\Support\Facades\Event::class,
        'File' => Syscodes\Components\Support\Facades\File::class,
        'Http' => Syscodes\Components\Support\Facades\Http::class,
        'Lang' => Syscodes\Components\Support\Facades\Lang::class,
        'Log' => Syscodes\Components\Support\Facades\Log::class,
        'Plaze' => Syscodes\Components\Support\Facades\Plaze::class,
        'Prime' => Syscodes\Components\Support\Facades\Prime::class,
        'Redirect' => Syscodes\Components\Support\Facades\Redirect::class,
        'Redis' => Syscodes\Components\Support\Facades\Redis::class,
        'Request' => Syscodes\Components\Support\Facades\Request::class,
        'Response' => Syscodes\Components\Support\Facades\Response::class,
        'Route' => Syscodes\Components\Support\Facades\Route::class,
        'Session' => Syscodes\Components\Support\Facades\Session::class,
        'URL' => Syscodes\Components\Support\Facades\URL::class,
        'View' => Syscodes\Components\Support\Facades\View::class,

    ],

];