<?php

use Lenevor\Sys\Logger\Log;

return [
	/*
	|------------------------------------------------------------------------
	| Error Logging Threshold
	|------------------------------------------------------------------------
	|
	| You can enable error logging by setting a threshold over zero. The
	| threshold determines what gets logged. Threshold options are:
	|
	| Log::L_NONE    = 0    -  Disables logging, Error logging TURNED OFF
	| Log::L_ALL     = 99   -  All Messages
	| Log::L_DEBUG   = 100  -  Detailed debug information
	| Log::L_INFO    = 200  -  Interesting events, like user logging in, etc
	| Log::L_WARNING = 300  -  Exceptional occurrences that are not errors
	| Log::L_ERROR   = 400  -  Don't need immediate action, but should be monitored
	| Log::L_NOTICES = 500  -  Normal but significant events
	|
	*/

	'logThreshold' => Log::L_NONE,

	/*
	|------------------------------------------------------------------------
	| Active report
	|------------------------------------------------------------------------
	|
	| Actived the schema to reports generated in the system.
	|
	*/

	'LogReport' => true,

	/*
	|------------------------------------------------------------------------
	| Date Format for Logs
	|------------------------------------------------------------------------
	|
	| Each item that is logged has an associated date. You can use PHP date
	| codes to set your own date formatting.
	|
	*/

	'logDateFormat' => 'Y-m-d H:i:s',

	/*
	|------------------------------------------------------------------------
	| Extension Name Logging
	|------------------------------------------------------------------------
	|
	| The default filename extension for log files. The default '.log' allows
	| for protecting the log files via basic scripting, when they are to be
	| stored under a publicly accessible directory.
	|
	| Note: Leaving it blank will default to '.log'.
	|
	*/

	'logExtension' => '.log',

	/*
	|------------------------------------------------------------------------
	| Error Logging Directory Path
	|------------------------------------------------------------------------
	|
	| Leave this BLANK unless you would like to set something other than the
	| default storage/logs directory. Use a full getServer path with
	| trailing slash.
	*/

	'logPath' => STO_PATH.'logs/',

	/*
	|------------------------------------------------------------------------
	| File System Permissions
	|------------------------------------------------------------------------
	|
	| The file system permissions to be applied on newly created log files.
	|
	| IMPORTANT: This MUST be an integer (no quotes) and you MUST use octal
	| integer notation (i.e. 0700, 0644, etc.)
	|
	*/

	'logPermission' => FILE_READ_MODE,

];