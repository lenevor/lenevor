<?php 

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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.1
 */

$sysDir  = dirname(__FILE__);
$rootDir = dirname($sysDir);

// Location to the paths config file
$config = require $rootDir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'paths.php';

// Define the absolute paths for configured directories
define('APP_PATH', realpath($config['path.app']).DIRECTORY_SEPARATOR);
define('BST_PATH', realpath($config['path.bootstrap']).DIRECTORY_SEPARATOR);
define('CON_PATH', realpath($config['path.config']).DIRECTORY_SEPARATOR);
define('DBD_PATH', realpath($config['path.database']).DIRECTORY_SEPARATOR);
define('PUB_PATH', realpath($config['path.index']).DIRECTORY_SEPARATOR);
define('RES_PATH', realpath($config['path.resources']).DIRECTORY_SEPARATOR);
define('RTR_PATH', realpath($config['path.routes']).DIRECTORY_SEPARATOR);
define('STO_PATH', realpath($config['path.storage']).DIRECTORY_SEPARATOR);
define('SYS_PATH', realpath($config['path.sys']).DIRECTORY_SEPARATOR);

// Call the file constants
require $rootDir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'constants.php';

// Activate the framework class autoloader
require $sysDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Autoloader'.DIRECTORY_SEPARATOR.'Autoloader.php';
// Call the class configuration Autoloader
require $sysDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Config'.DIRECTORY_SEPARATOR.'AutoloadConfig.php';

// Aliases of the class autoloader 
class_alias('Syscode\\Autoloader', 'Autoloader');

// Define the core classes to the autoloader
(new Autoloader)
    ->initialize(new Syscode\Config\AutoloadConfig())
    ->register();

// Load environment settings from .env files into $_SERVER and $_ENV 
(new Syscode\Config\ParserEnv($rootDir))
    ->load();