<?php

use Syscodes\Components\Support\Str;

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
	
	'default' => env('CACHE_DRIVER', 'file'),

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
		],

		'array' => [
			'driver' => 'array',
			'serialize' => false,
		],

		'database' => [
			'driver' => 'database',
			'table' => 'cache',
			'connection' => null
		],

		'file' => [
			'driver' => 'file',
			'path' => storagePath('framework/cache/data')
		],

		'memcached' => [
			'driver' => 'memcached',
			'persistentID' => env('MEMCACHED_PERSISTENT_ID'),
			'sasl' => [
				env('MEMCACHED_USERNAME'),
				env('MEMCACHED_PASSWORD'),
			],
			'options' => [

			],
			'servers' => [				
				'host' => env('MEMCACHED_HOST', '127.0.0.1'),
				'port' => env('MEMCACHED_PORT', 11211),
				'weight' => 100				
			],

		],

		'redis' => [
			'driver' => 'redis',
			'connection' => 'cache'
		]

	],

]; 