<?php

use Syscode\Support\Str;

return [
	
	/*
	|------------------------------------------------------------------------
	| Default Cache Store
	|------------------------------------------------------------------------
	|
	| The name of the preferred cache handler that should be used. If for 
	| some reason it is not available, the $backupHandler will be used in its 
	| place.
	|
	| Supported: "apc", "array", "database", "file", "memcached", "redis"
	|
	*/
	
	'driver' => env('CACHE_DRIVER', file),

	/*
	|------------------------------------------------------------------------
	| Default Backup Driver
	|------------------------------------------------------------------------
	|
	| The name of the handler that will be used in case the first one is
	| unreachable. Often, 'file' is used here since the filesystem is always
	| available, though that's not always practical for the app.
	|
	*/

	'backupDriver' => 'array',

	/*
	|------------------------------------------------------------------------
	| Cache Key Prefix
	|------------------------------------------------------------------------
	|
	| This string is added to all cache item names to help avoid collisions
	| if you run multiple applications with the same cache engine.
	|
	*/
	
	'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'Lenevor'), '_').'_cache'),

	/*
	|------------------------------------------------------------------------
	| Cache Stores
	|------------------------------------------------------------------------
	|
	| Here you may define all of the cache "stores" for your application as
	| well as their drivers. You may even define multiple stores for the same
	| cache driver to group types of items stored in your caches.
	|
	*/

	'stores' => [

		'apc' => [

			'driver'  => 'apc',
			'enabled' => true
			
		],

		'array' => [

			'driver'  => 'array'

		],

		'database' => [

			'driver'     => 'database',
			'table'      => 'cache',
			'connection' => null

		],

		'file' => [

			'driver'   => 'file',
			'lifetime' => 3600,
			'path'     => storagePath('/cache')

		],

		'servers' => [

			'memcached' => [

				'driver'       => 'memcached',
				'persistentID' => null,
				'sasl'         => [

					'username' => null,
					'password' => null

				],
				'options'      => [

				],
				'servers'      => [
					
					'host'         => '127.0.0.1',
					'port'         => 11211,
					'weight'       => 100
					
				],

			],

			'redis' => [

				'driver'   => 'redis',
				'host'     => '127.0.0.1',
				'password' => null,
				'port'     => 6379,
				'timeOut'  => 0

			]

		]

	],

]; 