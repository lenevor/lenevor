<?php

return [
	/*
	|------------------------------------------------------------------------
	| Error Logging Threshold
	|------------------------------------------------------------------------
	|
	| You can enable error logging by setting a threshold over zero. The
	| threshold determines what gets logged. Threshold options are:
	|
	| 0    NONE                -  Disables logging, Error logging TURNED OFF
	| 1    EMERGENCY MESSAGES  -  System is unusable
	| 2    ALERT MESSAGES      -  Action Must Be Taken Immediately
	| 3    CRITICAL MESSAGES   -  Application component unavailable, unexpected exception
	| 4    RUNTIME ERRORS      -  Don't need immediate action, but should be monitored.
	| 5    WARNINGS            -  Exceptional occurrences that are not errors
	| 6    NOTICES             -  Normal but significant events
	| 7    INFO     		   -  Interesting events, like user logging in, etc
	| 8    DEBUG    		   -  Detailed debug information	
	| 9    ALL MESSAGES
	|
	*/

	'logThreshold' => 0,

	/*
	|------------------------------------------------------------------------
	| Active Report
	|------------------------------------------------------------------------
	|
	| Actived the schema to reports generated in the system.
	|
	*/

	'LogReport' => true,

	/*
	|------------------------------------------------------------------------
	| Date Format or Logs
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