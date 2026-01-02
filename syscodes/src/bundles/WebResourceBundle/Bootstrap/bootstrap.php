<?php 

use Syscodes\Bundles\WebResourceBundle\Autoloader\Autoload;
use Syscodes\Bundles\WebResourceBundle\Autoloader\Autoloader;

/**
 * Lenevor Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.md.
 * It is also available through the world-wide-web at this URL:
 * https://lenevor.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Lenevor.com so we can send you a copy immediately.
 *
 * @package     Lenevor
 * @subpackage  Base
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

// Define the absolute paths for configured directories
if ( ! defined('APP_PATH')) define('APP_PATH', realpath($paths['path.app']).DIRECTORY_SEPARATOR);
if ( ! defined('CON_PATH')) define('CON_PATH', realpath($paths['path.config']).DIRECTORY_SEPARATOR);
if ( ! defined('SYS_PATH')) define('SYS_PATH', realpath($paths['path.sys']).DIRECTORY_SEPARATOR);

// Call the file constants
require CON_PATH.'constants.php';

/*
|------------------------------------------------------------------------
| Register The Composer Autoloader
|------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader 
| for our application. We will need it so that we do not have to worry 
| about loading any class of third party "manually".
|
*/

if (file_exists(COMPOSER_PATH)) {
    require COMPOSER_PATH;
}

// Register the autoloader
if ( ! class_exists(Syscodes\Bundles\WebResourceBundle\Autoloader\Autoload::class, false)) {
	require_once SYS_PATH.'src/bundles/WebResourceBundle/Autoloader/AutoloadConfig.php';
	require_once SYS_PATH.'src/bundles/WebResourceBundle/Autoloader/Autoload.php';
}

// Activate the framework class autoloader
require SYS_PATH.'src/bundles/WebResourceBundle/Autoloader/Autoloader.php';

// Define the core classes to the autoloader
Autoloader::instance()
    ->initialize(new Autoload())
    ->register();