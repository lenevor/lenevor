<?php

/*
|----------------------------------------------------------------------------
| Composer Path
|----------------------------------------------------------------------------
|
| The path that Composer's autoload file is expected to live. By default,
| the vendor folder is in the Root directory, but you can customize that here.
|
*/

defined('COMPOSER_PATH') OR define('COMPOSER_PATH', dirname(__DIR__).'/vendor/autoload.php');

/*
|----------------------------------------------------------------------------
| File and Directory Modes
|----------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working with 
| the file system.  The defaults are fine on servers with proper security, 
| but you may wish (or even need) to change the values in certain 
| environments. Octal values should always be used to set the mode correctly.
|
*/

defined('FILE_READ_MODE')  		OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') 		OR define('FILE_WRITE_MODE', 0666);
defined('FILE_READ_WRITE_MODE') OR define('FILE_READ_WRITE_MODE', 0777); 
defined('DIR_READ_MODE')   		OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  		OR define('DIR_WRITE_MODE', 0755);
defined('DIR_READ_WRITE_MODE')  OR define('DIR_READ_WRITE_MODE', 0777);