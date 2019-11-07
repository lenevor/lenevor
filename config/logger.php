<?php

return [

	/*
	|------------------------------------------------------------------------
	| Default Log Driver
	|------------------------------------------------------------------------
	|
	| This option defines the default log driver that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the drivers defined in the "logDriver" configuration array.
	|
	*/

	'default' => env('LOG_DRIVER', 'file'),

	/*
	|------------------------------------------------------------------------
	| Date Format or Logs
	|------------------------------------------------------------------------
	|
	| Each item that is logged has an associated date. You can use PHP date
	| codes to set your own date formatting.
	|
	*/

	'dateFormat' => 'Y-m-d H:i:s',
	
	/*
	|------------------------------------------------------------------------
	| Log Handlers
	|------------------------------------------------------------------------
	|
	| The logging system supports multiple actions to be taken when something
	| is logged. This is done by allowing for multiple Handlers, special classes
	| designed to write the log to their chosen destinations, whether that is
	| a file on the getServer, a cloud-based service, or even taking actions such
	| as emailing the dev team.
	|
	| Handlers are executed in the order defined in this array, starting with
	| the handler on top and continuing down.
	*/

	'handlers' => [

		'file' => [
			'driver' => 'file',
			'path' => storagePath('logs'),
			'extension' => 'log',
			'permission' => FILE_READ_MODE,
		],

	],

];