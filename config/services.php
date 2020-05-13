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
        Syscode\Cache\CacheServiceProvider::class,
        Syscode\Database\DatabaseServiceProvider::class,
        Syscode\Encryption\EncryptionServiceProvider::class,
        Syscode\Events\EventServiceProvider::class,
        Syscode\Filesystem\FilesystemServiceProvider::class,
        Syscode\Session\SessionServiceProvider::class,
        Syscode\Translation\TranslationServiceProvider::class,
        Syscode\View\ViewServiceProvider::class,


        /**
         * Application Service Providers
         */
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

        'App' => Syscode\Support\Facades\App::class,
        'Cache' => Syscode\Support\Facades\Cache::class,
        'Config' => Syscode\Support\Facades\Config::class,
        'Crypt' => Syscode\Support\Facades\Crypt::class,
        'DB' => Syscode\Support\Facades\DB::class,
        'Event' => Syscode\Support\Facades\Event::class,
        'File' => Syscode\Support\Facades\File::class,
        'Http' => Syscode\Support\Facades\Http::class,
        'Lang' => Syscode\Support\Facades\Lang::class,
        'Log' => Syscode\Support\Facades\Log::class,
        'Plaze' => Syscode\Support\Facades\Plaze::class,
        'Prime' => Syscode\Support\Facades\Prime::class,
        'Redirect' => Syscode\Support\Facades\Redirect::class,
        'Redis' => Syscode\Support\Facades\Redis::class,
        'Request' => Syscode\Support\Facades\Request::class,
        'Response' => Syscode\Support\Facades\Response::class,
        'Route' => Syscode\Support\Facades\Route::class,
        'Session' => Syscode\Support\Facades\Session::class,
        'URL' => Syscode\Support\Facades\URL::class,
        'View' => Syscode\Support\Facades\View::class,

    ],

];