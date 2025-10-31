<?php

return [
	
    /*
    |------------------------------------------------------------------------
    | Default Database Connection Name                                                       
    |------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course you 
    | may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |------------------------------------------------------------------------
    | Migration Repository Table
    |------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of the 
    | migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |------------------------------------------------------------------------
    | Directory Migration And Seeds
    |------------------------------------------------------------------------
    |
    | The directory that holds the Migrations and Seeds directories.
    |
    */

    'filePath' => database_path().'/',

    /*
    |------------------------------------------------------------------------
    | Database Connections
    |------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Zaucel is shown below to make development simple.
    |
    | All database work in Zaucel is done through the PHP PDO facilities so 
    | make sure you have the driver for your particular database of choice 
    | installed on your machine before you begin development.
    |
    */

    'connections' => [
        
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'lenevor'),
            'username' => env('DB_USERNAME', 'home'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lenevor'),
            'username' => env('DB_USERNAME', 'home'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer'
        ],
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('data/database.sqlite')),
            'prefix' => ''
        ],
        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'lenevor'),
            'username' => env('DB_USERNAME', 'home'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => ''
        ],
    
    ],

    /*
    |------------------------------------------------------------------------
    | Redis Databases
    |------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Zaucel makes it easy to dig right in.
    |
    */

    'redis' => [

        'cluster' => false,
        'default' => [
            'host'     => 'localhost',
            'password' => null,
            'port'     => 6379,
            'database' => 0
        ],
        'cache' => [
            'host'     => 'localhost',
            'password' => null,
            'port'     => 6379,
            'database' => 1
        ],

    ],

];