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

    'default' => 'mysql',

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

    'filePath' => DBD_PATH,

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

            'driver'    => 'mysql',
            'host'      =>'localhost',
            'port'      => 3306,
            'database'  => '',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null
        
        ],

        'pgsql' => [

            'driver'   => 'pgsql',
            'host'     => 'localhost',
            'port'     => 5432,
            'database' => '',
            'username' => 'root',
            'password' => '',
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
            'sslmode'  => 'prefer'

        ],

        'sqlite' => [

            'driver' => 'sqlite',
            'database' => 'database.sqlite',
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
            
        ]

    ],

];