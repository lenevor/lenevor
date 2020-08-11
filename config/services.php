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
        Syscodes\Cache\CacheServiceProvider::class,
        Syscodes\Database\DatabaseServiceProvider::class,
        Syscodes\Encryption\EncryptionServiceProvider::class,
        Syscodes\Events\EventServiceProvider::class,
        Syscodes\Filesystem\FilesystemServiceProvider::class,
        Syscodes\Session\SessionServiceProvider::class,
        Syscodes\Translation\TranslationServiceProvider::class,
        Syscodes\View\ViewServiceProvider::class,


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

        'App' => Syscodes\Support\Facades\App::class,
        'Cache' => Syscodes\Support\Facades\Cache::class,
        'Config' => Syscodes\Support\Facades\Config::class,
        'Crypt' => Syscodes\Support\Facades\Crypt::class,
        'DB' => Syscodes\Support\Facades\DB::class,
        'Event' => Syscodes\Support\Facades\Event::class,
        'File' => Syscodes\Support\Facades\File::class,
        'Http' => Syscodes\Support\Facades\Http::class,
        'Lang' => Syscodes\Support\Facades\Lang::class,
        'Log' => Syscodes\Support\Facades\Log::class,
        'Plaze' => Syscodes\Support\Facades\Plaze::class,
        'Prime' => Syscodes\Support\Facades\Prime::class,
        'Redirect' => Syscodes\Support\Facades\Redirect::class,
        'Redis' => Syscodes\Support\Facades\Redis::class,
        'Request' => Syscodes\Support\Facades\Request::class,
        'Response' => Syscodes\Support\Facades\Response::class,
        'Route' => Syscodes\Support\Facades\Route::class,
        'Session' => Syscodes\Support\Facades\Session::class,
        'URL' => Syscodes\Support\Facades\URL::class,
        'View' => Syscodes\Support\Facades\View::class,

    ],

];